<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\ClientService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ClientController extends AbstractAuthenticatedController
{
    public function __construct(
        TokenStorageInterface $tokenStorage,
        JWTTokenManagerInterface $jwtManager,
        LoggerInterface $logger,
        private readonly ClientService $clientService,
    ) {
        parent::__construct($tokenStorage, $jwtManager, $logger);
    }

    #[Route('/api/clients/{id}', name: 'api_clients_update_called_back', methods: ['PATCH'])]
    public function updateCalledBack(int $id, Request $request): JsonResponse
    {
        // Only Owner and Receptionist can manage clients
        if ($denied = $this->denyAccessUnlessOwnerOrReceptionist()) {
            return $denied;
        }

        $garageId = $this->getAuthenticatedGarageId($id);
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);

        $this->logger->info('Update client called back status', [
            'garage_id' => $garageId,
            'client_id' => $id,
        ]);

        if (!isset($data['isClientCalledBack'])) {
            $this->logger->warning('Missing isClientCalledBack field', [
                'garage_id' => $garageId,
                'client_id' => $id,
            ]);

            return $this->json(['error' => 'Missing required field: isClientCalledBack'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $this->clientService->updateClientCalledBackStatus($id, $data['isClientCalledBack']);

            $this->logger->info('Client called back status updated successfully', [
                'garage_id' => $garageId,
                'client_id' => $id,
            ]);

            return $this->json(['success' => true], JsonResponse::HTTP_OK);
        } catch (\RuntimeException $e) {
            $this->logger->error('Client not found', [
                'garage_id' => $garageId,
                'client_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    #[Route('/api/clients/{id}', name: 'api_clients_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        // Only Owner and Receptionist can manage clients
        if ($denied = $this->denyAccessUnlessOwnerOrReceptionist()) {
            return $denied;
        }

        $garageId = $this->getAuthenticatedGarageId($id);
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $data = json_decode($request->getContent(), true);

        $this->logger->info('Update client request', [
            'garage_id' => $garageId,
            'client_id' => $id,
        ]);

        if (!isset($data['firstName']) || !isset($data['lastName']) || !isset($data['phone'])) {
            $this->logger->warning('Missing required fields for client update', [
                'garage_id' => $garageId,
                'client_id' => $id,
                'data' => $data,
            ]);

            return $this->json(['error' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $this->clientService->updateClient(
                $id,
                $data['firstName'],
                $data['lastName'],
                $data['email'] ?? null,
                $data['phone'],
            );

            $this->logger->info('Client updated successfully', [
                'garage_id' => $garageId,
                'client_id' => $id,
            ]);

            return $this->json(['success' => true], JsonResponse::HTTP_OK);
        } catch (\RuntimeException $e) {
            $this->logger->error('Client not found', [
                'garage_id' => $garageId,
                'client_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
