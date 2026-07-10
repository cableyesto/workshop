<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\ReceptionistService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ReceptionistController extends AbstractAuthenticatedController
{
    public function __construct(
        TokenStorageInterface $tokenStorage,
        JWTTokenManagerInterface $jwtManager,
        LoggerInterface $logger,
        private readonly ReceptionistService $receptionistService,
    ) {
        parent::__construct($tokenStorage, $jwtManager, $logger);
    }

    #[Route('/api/receptionists', name: 'api_receptionists_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // Only Owner and Receptionist can manage receptionists
        if ($denied = $this->denyAccessUnlessOwnerOrReceptionist()) {
            return $denied;
        }

        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $receptionists = $this->receptionistService->getReceptionistsByGarageId($garageId);

        return $this->json($receptionists);
    }

    #[Route('/api/receptionists', name: 'api_receptionists_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Only Owner and Receptionist can manage receptionists
        if ($denied = $this->denyAccessUnlessOwnerOrReceptionist()) {
            return $denied;
        }

        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validate required fields
        $requiredFields = ['lastName', 'firstName', 'birthDate', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->logger->warning('Missing required field when creating receptionist', [
                    'field' => $field,
                    'garage_id' => $garageId,
                    'provided_fields' => array_keys($data),
                ]);
                return $this->json(['error' => "Missing required field: $field"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        try {
            $receptionist = $this->receptionistService->createReceptionist($garageId, $data);

            return $this->json([
                'id' => $receptionist->getId(),
                'lastName' => $receptionist->getLastName(),
                'firstName' => $receptionist->getFirstName(),
                'birthDate' => $receptionist->getBirthDate()->format('Y-m-d'),
                'hireDate' => $receptionist->getStartDate()?->format('Y-m-d'),
                'email' => $receptionist->getEmail(),
            ], JsonResponse::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Validation error when creating receptionist', [
                'error' => $e->getMessage(),
                'garage_id' => $garageId,
                'email' => $data['email'] ?? null,
            ]);
            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error when creating receptionist', [
                'error' => $e->getMessage(),
                'garage_id' => $garageId,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->json(['error' => 'Failed to create receptionist'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/receptionists/{id}', name: 'api_receptionists_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        // Only Owner and Receptionist can manage receptionists
        if ($denied = $this->denyAccessUnlessOwnerOrReceptionist()) {
            return $denied;
        }

        $garageId = $this->getAuthenticatedGarageId($id);
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            $this->logger->warning('Update receptionist failed: invalid JSON', [
                'receptionist_id' => $id,
                'garage_id' => $garageId,
                'raw_content' => $request->getContent(),
            ]);
            return $this->json(['error' => 'Invalid JSON'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $receptionist = $this->receptionistService->updateReceptionist($id, $garageId, $data);

            $this->logger->info('Receptionist updated successfully', [
                'receptionist_id' => $receptionist->getId(),
                'garage_id' => $garageId,
                'updated_fields' => array_keys($data),
            ]);

            return $this->json([
                'id' => $receptionist->getId(),
                'lastName' => $receptionist->getLastName(),
                'firstName' => $receptionist->getFirstName(),
                'birthDate' => $receptionist->getBirthDate()->format('Y-m-d'),
                'hireDate' => $receptionist->getStartDate()?->format('Y-m-d'),
                'email' => $receptionist->getEmail(),
            ]);
        } catch (\InvalidArgumentException $e) {
            $statusCode = match (true) {
                str_contains($e->getMessage(), 'not found') => JsonResponse::HTTP_NOT_FOUND,
                str_contains($e->getMessage(), 'Unauthorized') => JsonResponse::HTTP_FORBIDDEN,
                default => JsonResponse::HTTP_BAD_REQUEST,
            };

            $this->logger->warning('Validation error when updating receptionist', [
                'error' => $e->getMessage(),
                'receptionist_id' => $id,
                'garage_id' => $garageId,
                'provided_fields' => array_keys($data),
                'status_code' => $statusCode,
            ]);

            return $this->json(['error' => $e->getMessage()], $statusCode);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error when updating receptionist', [
                'error' => $e->getMessage(),
                'receptionist_id' => $id,
                'garage_id' => $garageId,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->json(['error' => 'Failed to update receptionist'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/receptionists/{id}', name: 'api_receptionists_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        // Only Owner and Receptionist can manage receptionists
        if ($denied = $this->denyAccessUnlessOwnerOrReceptionist()) {
            return $denied;
        }

        $garageId = $this->getAuthenticatedGarageId($id);
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        try {
            $this->receptionistService->deleteReceptionist($id, $garageId);

            $this->logger->info('Receptionist deleted successfully', [
                'receptionist_id' => $id,
                'garage_id' => $garageId,
            ]);

            return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (\InvalidArgumentException $e) {
            $statusCode = match (true) {
                str_contains($e->getMessage(), 'not found') => JsonResponse::HTTP_NOT_FOUND,
                str_contains($e->getMessage(), 'Unauthorized') => JsonResponse::HTTP_FORBIDDEN,
                default => JsonResponse::HTTP_BAD_REQUEST,
            };

            $this->logger->warning('Error deleting receptionist', [
                'error' => $e->getMessage(),
                'receptionist_id' => $id,
                'garage_id' => $garageId,
                'status_code' => $statusCode,
            ]);

            return $this->json(['error' => $e->getMessage()], $statusCode);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error when deleting receptionist', [
                'error' => $e->getMessage(),
                'receptionist_id' => $id,
                'garage_id' => $garageId,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->json(['error' => 'Failed to delete receptionist'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
