<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\CarService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CarController extends AbstractAuthenticatedController
{
    public function __construct(
        TokenStorageInterface $tokenStorage,
        JWTTokenManagerInterface $jwtManager,
        LoggerInterface $logger,
        private readonly CarService $carService,
    ) {
        parent::__construct($tokenStorage, $jwtManager, $logger);
    }

    #[Route('/api/cars/storage', name: 'api_cars_storage', methods: ['GET'])]
    public function storage(): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $cars = $this->carService->getStoredCars($garageId);

        return $this->json($cars);
    }

    #[Route('/api/cars/interventions', name: 'api_cars_interventions', methods: ['GET'])]
    public function interventions(): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $cars = $this->carService->getCarsWithInterventions($garageId);

        return $this->json($cars);
    }

    #[Route('/api/cars/license-plate', name: 'api_cars_update_storage_by_license_plate', methods: ['PATCH'])]
    public function updateStorageByLicensePlate(Request $request): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);

        $this->logger->info('Update car storage by license plate request', [
            'garage_id' => $garageId,
            'license_plate' => $data['licensePlate'] ?? 'missing',
            'is_stored' => $data['isStored'] ?? 'missing',
        ]);

        if (!isset($data['licensePlate']) || !isset($data['isStored'])) {
            $this->logger->warning('Missing required fields for car storage update', [
                'garage_id' => $garageId,
                'data' => $data,
            ]);

            return $this->json(['error' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $this->carService->updateCarStorageByLicensePlate(
                $data['licensePlate'],
                $data['isStored'],
                $garageId,
            );

            $this->logger->info('Car storage status updated successfully', [
                'garage_id' => $garageId,
                'license_plate' => $data['licensePlate'],
                'is_stored' => $data['isStored'],
            ]);

            return $this->json(['success' => true], JsonResponse::HTTP_OK);
        } catch (\RuntimeException $e) {
            $this->logger->error('Car not found for license plate', [
                'garage_id' => $garageId,
                'license_plate' => $data['licensePlate'],
                'error' => $e->getMessage(),
            ]);

            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
