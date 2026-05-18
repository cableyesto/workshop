<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Intervention;
use App\Enum\DocumentType;
use App\Enum\InterventionStatus;
use App\Enum\InterventionType;
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

            // Create intervention with default values
            $intervention = new Intervention();
            $intervention
                ->setCar($car)
                ->setDate(\DateTimeImmutable::createFromMutable(new \DateTime()))
                ->setStartTime(\DateTimeImmutable::createFromMutable(new \DateTime()))
                ->setStatus(InterventionStatus::Assigned)
                ->setType(InterventionType::Repair)  // Default - will be updated in Step 2
                ->setDocumentType(DocumentType::Invoice)   // Default - will be updated in Step 2
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

    #[Route('/api/interventions/{id}', name: 'api_interventions_update', methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);

        $this->logger->info('Update intervention request', [
            'garage_id' => $garageId,
            'intervention_id' => $id,
            'data' => $data,
        ]);

        try {
            $intervention = $this->interventionRepository->find($id);

            if (!$intervention) {
                return $this->json([
                    'error' => 'Intervention not found',
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            if ($intervention->getCar()->getClient()->getGarage()->getId() !== $garageId) {
                return $this->json([
                    'error' => 'Unauthorized',
                ], JsonResponse::HTTP_FORBIDDEN);
            }

            if (isset($data['interventionType'])) {
                $type = InterventionType::tryFrom($data['interventionType']);
                if ($type) {
                    $intervention->setType($type);
                }
            }

            if (isset($data['documentType'])) {
                $documentType = DocumentType::tryFrom($data['documentType']);
                if ($documentType) {
                    $intervention->setDocumentType($documentType);
                }
            }

            if (isset($data['clientRequest'])) {
                $intervention->setClientRequest($data['clientRequest'] ?: null);
            }

            if (isset($data['finalNote'])) {
                $intervention->setFinalNote($data['finalNote'] ?: null);
            }

            // Update mechanic
            if (isset($data['mechanicId'])) {
                $mechanicId = (int) $data['mechanicId'];
                $mechanic = $this->mechanicRepository->find($mechanicId);

                if (!$mechanic) {
                    $this->logger->warning('Mechanic not found for update', [
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
                    $this->logger->warning('Mechanic does not belong to garage for update', [
                        'garage_id' => $garageId,
                        'mechanic_id' => $mechanicId,
                    ]);

                    return $this->json([
                        'error' => 'Mechanic does not belong to this garage',
                    ], JsonResponse::HTTP_FORBIDDEN);
                }

                // Replace existing mechanics with new one
                $intervention->getMechanics()->clear();
                $intervention->addMechanic($mechanic);
            }

            // Update car (by license plate)
            if (isset($data['licensePlate'])) {
                $licensePlate = (string) $data['licensePlate'];
                $car = $this->carRepository->findByLicensePlateForGarage($licensePlate, $garageId);

                if (!$car) {
                    $this->logger->warning('Car not found for license plate update', [
                        'garage_id' => $garageId,
                        'license_plate' => $licensePlate,
                    ]);

                    return $this->json([
                        'error' => 'Car not found with this license plate',
                    ], JsonResponse::HTTP_NOT_FOUND);
                }

                $intervention->setCar($car);
            }

            // Update date
            if (isset($data['date'])) {
                try {
                    $date = \DateTimeImmutable::createFromFormat('Y-m-d', $data['date']);
                    if ($date === false) {
                        throw new \Exception('Invalid date format');
                    }
                    $intervention->setDate($date);
                } catch (\Exception $e) {
                    $this->logger->warning('Invalid date format', [
                        'garage_id' => $garageId,
                        'date' => $data['date'],
                    ]);

                    return $this->json([
                        'error' => 'Invalid date format. Expected: Y-m-d',
                    ], JsonResponse::HTTP_BAD_REQUEST);
                }
            }

            // Update start time
            if (isset($data['startTime'])) {
                try {
                    $startTime = \DateTimeImmutable::createFromFormat('H:i', $data['startTime']);
                    if ($startTime === false) {
                        throw new \Exception('Invalid time format');
                    }
                    $intervention->setStartTime($startTime);
                } catch (\Exception $e) {
                    $this->logger->warning('Invalid time format', [
                        'garage_id' => $garageId,
                        'startTime' => $data['startTime'],
                    ]);

                    return $this->json([
                        'error' => 'Invalid time format. Expected: H:i',
                    ], JsonResponse::HTTP_BAD_REQUEST);
                }
            }

            $this->interventionRepository->getEntityManager()->flush();

            $this->logger->info('Intervention updated successfully', [
                'garage_id' => $garageId,
                'intervention_id' => $id,
            ]);

            return $this->json(['success' => true], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Error updating intervention', [
                'garage_id' => $garageId,
                'intervention_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'An error occurred while updating the intervention',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/interventions/{id}', name: 'api_interventions_get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $this->logger->info('Get intervention request', [
            'garage_id' => $garageId,
            'intervention_id' => $id,
        ]);

        try {
            $intervention = $this->interventionRepository->find($id);

            if (!$intervention) {
                return $this->json([
                    'error' => 'Intervention not found',
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            // Check authorization
            if ($intervention->getCar()->getClient()->getGarage()->getId() !== $garageId) {
                return $this->json([
                    'error' => 'Unauthorized',
                ], JsonResponse::HTTP_FORBIDDEN);
            }

            $firstMechanic = Collection::make($intervention->getMechanics())->first();

            return $this->json([
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
                'date' => $intervention->getDate()?->format('Y-m-d'),
                'startTime' => $intervention->getStartTime()?->format('H:i'),
                'status' => $intervention->getStatus()?->value,
                'interventionType' => $intervention->getType()?->value,
                'documentType' => $intervention->getDocumentType()?->value,
                'clientRemark' => !empty($intervention->getClientRequest()),
                'clientRequest' => $intervention->getClientRequest(),
                'interventionEndRemark' => !empty($intervention->getFinalNote()),
                'finalNote' => $intervention->getFinalNote(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error getting intervention', [
                'garage_id' => $garageId,
                'intervention_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->json([
                'error' => 'An error occurred while fetching the intervention',
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
