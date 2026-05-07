<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\GarageService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GarageController extends AbstractController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly GarageService $garageService,
    ) {
    }

    #[Route('/api/garages/{id}', name: 'api_garage_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return $this->json(['error' => 'Authentication required'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $payload = $this->jwtManager->decode($token);
        if (!$payload) {
            return $this->json(['error' => 'Invalid token'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Authorization
        $hasAccess = false;

        // Owner: check if id is in garage_ids array
        if (isset($payload['garage_ids']) && is_array($payload['garage_ids'])) {
            $hasAccess = in_array($id, $payload['garage_ids'], true);
        }

        // Receptionist: check if id matches garage_id
        if (isset($payload['garage_id'])) {
            $hasAccess = ($payload['garage_id'] === $id);
        }

        if (!$hasAccess) {
            return $this->json(['error' => 'Access denied to this garage'], JsonResponse::HTTP_FORBIDDEN);
        }

        $garageData = $this->garageService->getGarageById($id);
        if (!$garageData) {
            return $this->json(['error' => 'Garage not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($garageData);
    }
}
