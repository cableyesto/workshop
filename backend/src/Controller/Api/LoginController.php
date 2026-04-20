<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\OwnerRepository;
use App\Repository\ReceptionistRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
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
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
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
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        // Generate JWT token
        $token = $this->jwtManager->create($user);

        return $this->json(['token' => $token]);
    }
}
