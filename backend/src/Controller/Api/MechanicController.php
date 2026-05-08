<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\MechanicService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class MechanicController extends AbstractAuthenticatedController
{
    public function __construct(
        TokenStorageInterface $tokenStorage,
        JWTTokenManagerInterface $jwtManager,
        LoggerInterface $logger,
        private readonly MechanicService $mechanicService,
    ) {
        parent::__construct($tokenStorage, $jwtManager, $logger);
    }

    #[Route('/api/mechanics', name: 'api_mechanics_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $mechanics = $this->mechanicService->getMechanicsByGarageId($garageId);

        return $this->json($mechanics);
    }

    #[Route('/api/mechanics', name: 'api_mechanics_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validate required fields
        $requiredFields = ['lastName', 'firstName', 'birthDate', 'pin'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->logger->warning('Missing required field when creating mechanic', [
                    'field' => $field,
                    'garage_id' => $garageId,
                    'provided_fields' => array_keys($data),
                ]);
                return $this->json(['error' => "Missing required field: $field"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        try {
            $mechanic = $this->mechanicService->createMechanic($garageId, $data);

            return $this->json([
                'id' => $mechanic->getId(),
                'lastName' => $mechanic->getLastName(),
                'firstName' => $mechanic->getFirstName(),
                'birthDate' => $mechanic->getBirthDate()->format('Y-m-d'),
                'hireDate' => $mechanic->getStartDate()?->format('Y-m-d'),
            ], JsonResponse::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Validation error when creating mechanic', [
                'error' => $e->getMessage(),
                'garage_id' => $garageId,
                'pin' => $data['pin'] ?? null,
            ]);
            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error when creating mechanic', [
                'error' => $e->getMessage(),
                'garage_id' => $garageId,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->json(['error' => 'Failed to create mechanic'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/mechanics/{id}', name: 'api_mechanics_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId($id);
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            $this->logger->warning('Update mechanic failed: invalid JSON', [
                'mechanic_id' => $id,
                'garage_id' => $garageId,
                'raw_content' => $request->getContent(),
            ]);
            return $this->json(['error' => 'Invalid JSON'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $mechanic = $this->mechanicService->updateMechanic($id, $garageId, $data);

            $this->logger->info('Mechanic updated successfully', [
                'mechanic_id' => $mechanic->getId(),
                'garage_id' => $garageId,
                'updated_fields' => array_keys($data),
            ]);

            return $this->json([
                'id' => $mechanic->getId(),
                'lastName' => $mechanic->getLastName(),
                'firstName' => $mechanic->getFirstName(),
                'birthDate' => $mechanic->getBirthDate()->format('Y-m-d'),
                'hireDate' => $mechanic->getStartDate()?->format('Y-m-d'),
            ]);
        } catch (\InvalidArgumentException $e) {
            $statusCode = match (true) {
                str_contains($e->getMessage(), 'not found') => JsonResponse::HTTP_NOT_FOUND,
                str_contains($e->getMessage(), 'Unauthorized') => JsonResponse::HTTP_FORBIDDEN,
                default => JsonResponse::HTTP_BAD_REQUEST,
            };

            $this->logger->warning('Validation error when updating mechanic', [
                'error' => $e->getMessage(),
                'mechanic_id' => $id,
                'garage_id' => $garageId,
                'provided_fields' => array_keys($data),
                'status_code' => $statusCode,
            ]);

            return $this->json(['error' => $e->getMessage()], $statusCode);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error when updating mechanic', [
                'error' => $e->getMessage(),
                'mechanic_id' => $id,
                'garage_id' => $garageId,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->json(['error' => 'Failed to update mechanic'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/mechanics/{id}', name: 'api_mechanics_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId($id);
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        try {
            $this->mechanicService->deleteMechanic($id, $garageId);

            $this->logger->info('Mechanic deleted successfully', [
                'mechanic_id' => $id,
                'garage_id' => $garageId,
            ]);

            return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (\InvalidArgumentException $e) {
            $statusCode = match (true) {
                str_contains($e->getMessage(), 'not found') => JsonResponse::HTTP_NOT_FOUND,
                str_contains($e->getMessage(), 'Unauthorized') => JsonResponse::HTTP_FORBIDDEN,
                default => JsonResponse::HTTP_BAD_REQUEST,
            };

            $this->logger->warning('Error deleting mechanic', [
                'error' => $e->getMessage(),
                'mechanic_id' => $id,
                'garage_id' => $garageId,
                'status_code' => $statusCode,
            ]);

            return $this->json(['error' => $e->getMessage()], $statusCode);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error when deleting mechanic', [
                'error' => $e->getMessage(),
                'mechanic_id' => $id,
                'garage_id' => $garageId,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->json(['error' => 'Failed to delete mechanic'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
