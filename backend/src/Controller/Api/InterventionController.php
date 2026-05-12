<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Intervention;
use App\Enum\InterventionStatus;
use App\Repository\CarRepository;
use App\Repository\InterventionRepository;
use App\Repository\MechanicRepository;
use Illuminate\Support\Collection;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class InterventionController extends AbstractAuthenticatedController
{
    public function __construct(
        TokenStorageInterface $tokenStorage,
        JWTTokenManagerInterface $jwtManager,
        LoggerInterface $logger,
        private readonly InterventionRepository $interventionRepository,
        private readonly MechanicRepository $mechanicRepository,
        private readonly CarRepository $carRepository,
    ) {
        parent::__construct($tokenStorage, $jwtManager, $logger);
    }

    #[Route('/api/interventions', name: 'api_interventions_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);

        $this->logger->info('Create intervention request', [
            'garage_id' => $garageId,
            'data' => $data,
        ]);

        // Validate required fields
        if (!isset($data['mechanicId']) || !isset($data['licensePlate'])) {
            $this->logger->warning('Missing required fields for intervention creation', [
                'garage_id' => $garageId,
                'data' => $data,
            ]);

            return $this->json([
                'error' => 'Missing required fields: mechanicId and licensePlate',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $mechanicId = (int) $data['mechanicId'];
        $licensePlate = (string) $data['licensePlate'];

        try {
            // Validate mechanic exists and belongs to garage
            $mechanic = $this->mechanicRepository->find($mechanicId);
            if (!$mechanic) {
                $this->logger->warning('Mechanic not found', [
                    'garage_id' => $garageId,
                    'mechanic_id' => $mechanicId,
                ]);

                return $this->json([
                    'error' => 'Mechanic not found',
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            // Check if mechanic works at this garage
            $mechanicBelongsToGarage = Collection::make($mechanic->getGarages())
                ->contains(fn($garage) => $garage->getId() === $garageId);

            if (!$mechanicBelongsToGarage) {
                $this->logger->warning('Mechanic does not belong to garage', [
                    'garage_id' => $garageId,
                    'mechanic_id' => $mechanicId,
                ]);

                return $this->json([
                    'error' => 'Mechanic does not belong to this garage',
                ], JsonResponse::HTTP_FORBIDDEN);
            }

            // Find car by license plate for this garage
            $car = $this->carRepository->findByLicensePlateForGarage($licensePlate, $garageId);
            if (!$car) {
                $this->logger->warning('Car not found for license plate', [
                    'garage_id' => $garageId,
                    'license_plate' => $licensePlate,
                ]);

                return $this->json([
                    'error' => 'Car not found with this license plate',
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            // Create intervention
            $intervention = new Intervention();
            $intervention
                ->setCar($car)
                ->setDate(\DateTimeImmutable::createFromMutable(new \DateTime()))
                ->setStartTime(\DateTimeImmutable::createFromMutable(new \DateTime()))
                ->setStatus(InterventionStatus::Assigned)
                ->addMechanic($mechanic);

            // Persist
            $em = $this->interventionRepository->getEntityManager();
            $em->persist($intervention);
            $em->flush();

            $this->logger->info('Intervention created successfully', [
                'garage_id' => $garageId,
                'intervention_id' => $intervention->getId(),
                'mechanic_id' => $mechanicId,
                'car_id' => $car->getId(),
            ]);

            return $this->json([
                'id' => $intervention->getId(),
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Error creating intervention', [
                'garage_id' => $garageId,
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'An error occurred while creating the intervention',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/interventions/search', name: 'api_interventions_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $mechanicId = $request->query->getInt('mechanicId');
        $licensePlate = $request->query->get('licensePlate', '');

        $this->logger->info('Search intervention request', [
            'garage_id' => $garageId,
            'mechanic_id' => $mechanicId,
            'license_plate' => $licensePlate,
        ]);

        if (!$mechanicId || !$licensePlate) {
            $this->logger->warning('Missing required parameters for intervention search', [
                'garage_id' => $garageId,
                'mechanic_id' => $mechanicId,
                'license_plate' => $licensePlate,
            ]);

            return $this->json([
                'error' => 'Missing required parameters: mechanicId and licensePlate',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $intervention = $this->interventionRepository->findActiveByMechanicAndLicensePlate(
                $mechanicId,
                $licensePlate,
                $garageId,
            );

            if ($intervention) {
                $this->logger->info('Active intervention found', [
                    'garage_id' => $garageId,
                    'intervention_id' => $intervention->getId(),
                ]);

                $firstMechanic = Collection::make($intervention->getMechanics())->first();

                return $this->json([
                    'found' => true,
                    'intervention' => [
                        'id' => $intervention->getId(),
                        'mechanic' => [
                            'id' => $firstMechanic?->getId(),
                            'firstName' => $firstMechanic?->getFirstName() ?? '',
                            'lastName' => $firstMechanic?->getLastName() ?? '',
                        ],
                        'car' => [
                            'id' => $intervention->getCar()->getId(),
                            'licensePlate' => $intervention->getCar()->getLicensePlate(),
                        ],
                        'startDate' => $intervention->getDate()?->format('Y-m-d'),
                        'startTime' => $intervention->getStartTime()?->format('H:i'),
                        'status' => $this->translateStatus($intervention->getStatus()?->value),
                        'interventionType' => $intervention->getType()?->value,
                        'documentType' => $intervention->getDocumentType()?->value,
                        'clientRemark' => !empty($intervention->getClientRequest()),
                        'clientNeed' => $intervention->getClientRequest(),
                        'interventionEndRemark' => !empty($intervention->getFinalNote()),
                        'interventionEndNote' => $intervention->getFinalNote(),
                    ],
                ]);
            }

            $this->logger->info('No active intervention found', [
                'garage_id' => $garageId,
                'mechanic_id' => $mechanicId,
                'license_plate' => $licensePlate,
            ]);

            return $this->json(['found' => false]);
        } catch (\Exception $e) {
            $this->logger->error('Error searching intervention', [
                'garage_id' => $garageId,
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'An error occurred while searching for intervention',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function translateStatus(?string $status): string
    {
        return match ($status) {
            'Assigned' => 'Affectée',
            'In Progress' => 'En cours',
            'Paused' => 'En pause',
            'Stopped' => 'Terminée',
            default => $status ?? '',
        };
    }
}
