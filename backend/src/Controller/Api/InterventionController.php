<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\InterventionRepository;
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
    ) {
        parent::__construct($tokenStorage, $jwtManager, $logger);
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
