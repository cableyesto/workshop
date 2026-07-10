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

    #[Route('/api/cars', name: 'api_cars_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $cars = $this->carService->getAllCars($garageId);

        return $this->json($cars);
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

    #[Route('/api/cars/restitution', name: 'api_cars_restitution', methods: ['GET'])]
    public function restitution(): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $cars = $this->carService->getCarsForRestitution($garageId);

        return $this->json($cars);
    }

    #[Route('/api/cars/{id}/storage', name: 'api_cars_update_storage_by_id', methods: ['PATCH'])]
    public function updateStorageById(int $id, Request $request): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);

        $this->logger->info('Update car storage by ID request', [
            'garage_id' => $garageId,
            'car_id' => $id,
            'is_stored' => $data['isStored'] ?? 'missing',
        ]);

        if (!isset($data['isStored'])) {
            $this->logger->warning('Missing required field for car storage update', [
                'garage_id' => $garageId,
                'car_id' => $id,
            ]);

            return $this->json(['error' => 'Missing required field: isStored'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $this->carService->updateCarStorageById($id, $data['isStored'], $garageId);

            $this->logger->info('Car storage status updated successfully', [
                'garage_id' => $garageId,
                'car_id' => $id,
                'is_stored' => $data['isStored'],
            ]);

            return $this->json(['success' => true], JsonResponse::HTTP_OK);
        } catch (\RuntimeException $e) {
            $this->logger->error('Car not found', [
                'garage_id' => $garageId,
                'car_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
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

    #[Route('/api/cars/with-client', name: 'api_cars_create_with_client', methods: ['POST'])]
    public function createWithClient(Request $request): JsonResponse
    {
        // Only Owner and Receptionist can create cars with clients
        if ($denied = $this->denyAccessUnlessOwnerOrReceptionist()) {
            return $denied;
        }

        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);

        $this->logger->info('Create car with client request', [
            'garage_id' => $garageId,
        ]);

        // Validate required client fields
        if (
            !isset($data['client']['firstName']) ||
            !isset($data['client']['lastName']) ||
            !isset($data['client']['phone'])
        ) {
            $this->logger->warning('Missing required client fields', [
                'garage_id' => $garageId,
                'data' => $data,
            ]);

            return $this->json(['error' => 'Missing required client fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validate required car fields
        if (
            !isset($data['car']['manufacturer']) ||
            !isset($data['car']['model']) ||
            !isset($data['car']['licensePlate']) ||
            !isset($data['car']['color'])
        ) {
            $this->logger->warning('Missing required car fields', [
                'garage_id' => $garageId,
                'data' => $data,
            ]);

            return $this->json(['error' => 'Missing required car fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $car = $this->carService->createCarWithClient(
                $garageId,
                $data['client']['firstName'],
                $data['client']['lastName'],
                $data['client']['email'] ?? null,
                $data['client']['phone'],
                $data['car']['manufacturer'],
                $data['car']['model'],
                $data['car']['licensePlate'],
                $data['car']['color'],
                $data['car']['registrationYear'] ?? null,
                $data['car']['registrationMonth'] ?? null,
                $data['car']['mileage'] ?? null,
            );

            $this->logger->info('Car with client created successfully', [
                'garage_id' => $garageId,
                'car_id' => $car['id'],
                'client_id' => $car['client']['id'],
            ]);

            return $this->json([
                'success' => true,
                'car' => $car,
            ], JsonResponse::HTTP_CREATED);
        } catch (\RuntimeException $e) {
            $this->logger->error('Error creating car with client', [
                'garage_id' => $garageId,
                'error' => $e->getMessage(),
            ]);

            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error creating car with client', [
                'garage_id' => $garageId,
                'error' => $e->getMessage(),
            ]);

            return $this->json(['error' => 'An error occurred while creating the car'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/cars/{id}', name: 'api_cars_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId($id);
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);

        $this->logger->info('Update car request', [
            'garage_id' => $garageId,
            'car_id' => $id,
        ]);

        if (
            !isset($data['manufacturer']) ||
            !isset($data['model']) ||
            !isset($data['licensePlate']) ||
            !isset($data['color'])
        ) {
            $this->logger->warning('Missing required fields for car update', [
                'garage_id' => $garageId,
                'car_id' => $id,
                'data' => $data,
            ]);

            return $this->json(['error' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $this->carService->updateCar(
                $id,
                $data['manufacturer'],
                $data['model'],
                $data['licensePlate'],
                $data['color'],
                $data['registrationYear'] ?? null,
                $data['registrationMonth'] ?? null,
                $data['mileage'] ?? null,
            );

            $this->logger->info('Car updated successfully', [
                'garage_id' => $garageId,
                'car_id' => $id,
            ]);

            return $this->json(['success' => true], JsonResponse::HTTP_OK);
        } catch (\RuntimeException $e) {
            $this->logger->error('Car not found', [
                'garage_id' => $garageId,
                'car_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
