<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractAuthenticatedController extends AbstractController
{
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly JWTTokenManagerInterface $jwtManager,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Extract and validate garage ID from JWT token
     *
     * Supports all user types:
     * - Owner: uses first garage from garage_ids array
     * - Receptionist: uses garage_id
     * - Mechanic: uses garage_id
     *
     * @param int|null $resourceId Optional resource ID for logging context
     * @return int|JsonResponse Returns garage ID on success, JsonResponse on failure
     */
    protected function getAuthenticatedGarageId(?int $resourceId = null): int|JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            $this->logger->warning('Authentication required', [
                'resource_id' => $resourceId,
            ]);
            return $this->json(['error' => 'Authentication required'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $payload = $this->jwtManager->decode($token);
        if (!$payload) {
            $this->logger->warning('Invalid token', [
                'resource_id' => $resourceId,
            ]);
            return $this->json(['error' => 'Invalid token'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Extract garage_id from JWT
        $garageId = null;

        // Receptionist & Mechanic: single garage_id
        if (isset($payload['garage_id'])) {
            $garageId = $payload['garage_id'];
        }

        // Owner: multiple garage_ids (use first one)
        if (isset($payload['garage_ids']) && is_array($payload['garage_ids'])) {
            $garageId = $payload['garage_ids'][0] ?? null;
        }

        if (!$garageId) {
            $this->logger->warning('No garage context in token', [
                'resource_id' => $resourceId,
                'payload' => $payload,
            ]);
            return $this->json(['error' => 'No garage context in token'], JsonResponse::HTTP_FORBIDDEN);
        }

        return $garageId;
    }
}
