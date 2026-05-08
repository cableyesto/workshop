<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Owner;
use App\Entity\Receptionist;
use App\Repository\GarageRepository;
use App\Repository\OwnerRepository;
use App\Repository\ReceptionistRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Login controller for Owner and Receptionist authentication.
 * Handles email/password login and returns JWT token.
 */
final class LoginController extends AbstractController
{
    public function __construct(
        private readonly OwnerRepository $ownerRepository,
        private readonly ReceptionistRepository $receptionistRepository,
        private readonly GarageRepository $garageRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly LoggerInterface $securityLogger,
    ) {
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        // Parse request
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return $this->json(['error' => 'Missing email or password'], 400);
        }

        // Find user (Owner or Receptionist)
        $user = $this->ownerRepository->findOneBy(['email' => $data['email']])
            ?? $this->receptionistRepository->findOneBy(['email' => $data['email']]);

        // Validate credentials
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            $this->securityLogger->warning('Failed login attempt', [
                'email' => $data['email'],
                'ip' => $request->getClientIp(),
            ]);

            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        // Handle garage-scoped authentication for Receptionist
        if ($user instanceof Receptionist) {
            // Receptionist MUST provide SIRET
            if (!isset($data['siret'])) {
                $this->securityLogger->warning('Receptionist login missing SIRET', [
                    'email' => $user->getEmail(),
                    'ip' => $request->getClientIp(),
                ]);

                return $this->json(['error' => 'SIRET required for receptionist login'], 400);
            }

            // Find garage by SIRET
            $garage = $this->garageRepository->findOneBy(['siretNumber' => $data['siret']]);

            // Validate garage exists and receptionist works there
            if (!$garage || !$user->getGarages()->contains($garage)) {
                $this->securityLogger->warning('Receptionist login invalid garage', [
                    'email' => $user->getEmail(),
                    'siret' => $data['siret'],
                    'ip' => $request->getClientIp(),
                ]);

                return $this->json(['error' => 'Invalid credentials'], 401);
            }

            // Store garage context in request attributes for JWT event listener
            $request->attributes->set('garage_context', [
                'garage_id' => $garage->getId(),
                'garage_siret' => $garage->getSiretNumber(),
            ]);
        } elseif ($user instanceof Owner) {
            // Owner: SIRET is ignored even if provided
            // Multi-garage access - no garage context needed
        }

        // Generate JWT access token (8 hours)
        $token = $this->jwtManager->create($user);

        // Return access token
        return $this->json([
            'token' => $token,
        ]);
    }

}
