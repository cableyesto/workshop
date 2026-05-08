<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\MechanicService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class MechanicController extends AbstractController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly MechanicService $mechanicService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/api/mechanics', name: 'api_mechanics_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return $this->json(['error' => 'Authentication required'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $payload = $this->jwtManager->decode($token);
        if (!$payload) {
            return $this->json(['error' => 'Invalid token'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Get garage_id from JWT (receptionist) or garage_ids (owner)
        $garageId = null;

        // Receptionist: single garage_id
        if (isset($payload['garage_id'])) {
            $garageId = $payload['garage_id'];
        }

        // Owner: multiple garage_ids (for now, return mechanics from all garages)
        // TODO: If frontend needs filtering by specific garage, add query parameter
        if (isset($payload['garage_ids']) && is_array($payload['garage_ids'])) {
            // For simplicity, get mechanics from first garage
            // In production, you might want to fetch from all garages or add filtering
            $garageId = $payload['garage_ids'][0] ?? null;
        }

        if (!$garageId) {
            return $this->json(['error' => 'No garage context in token'], JsonResponse::HTTP_FORBIDDEN);
        }

        $mechanics = $this->mechanicService->getMechanicsByGarageId($garageId);

        return $this->json($mechanics);
    }

    #[Route('/api/mechanics', name: 'api_mechanics_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return $this->json(['error' => 'Authentication required'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $payload = $this->jwtManager->decode($token);
        if (!$payload) {
            return $this->json(['error' => 'Invalid token'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $garageId = null;
        if (isset($payload['garage_id'])) {
            $garageId = $payload['garage_id'];
        } elseif (isset($payload['garage_ids']) && is_array($payload['garage_ids'])) {
            $garageId = $payload['garage_ids'][0] ?? null;
        }

        if (!$garageId) {
            return $this->json(['error' => 'No garage context in token'], JsonResponse::HTTP_FORBIDDEN);
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
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            $this->logger->warning('Update mechanic failed: authentication required', [
                'mechanic_id' => $id,
            ]);
            return $this->json(['error' => 'Authentication required'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $payload = $this->jwtManager->decode($token);
        if (!$payload) {
            $this->logger->warning('Update mechanic failed: invalid token', [
                'mechanic_id' => $id,
            ]);
            return $this->json(['error' => 'Invalid token'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $garageId = null;
        if (isset($payload['garage_id'])) {
            $garageId = $payload['garage_id'];
        } elseif (isset($payload['garage_ids']) && is_array($payload['garage_ids'])) {
            $garageId = $payload['garage_ids'][0] ?? null;
        }

        if (!$garageId) {
            $this->logger->warning('Update mechanic failed: no garage context in token', [
                'mechanic_id' => $id,
                'payload' => $payload,
            ]);
            return $this->json(['error' => 'No garage context in token'], JsonResponse::HTTP_FORBIDDEN);
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
            // Handle specific validation errors (not found, unauthorized, validation failed)
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
}
