<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\MechanicService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
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
            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to create mechanic'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
