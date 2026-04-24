<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Owner;
use App\Entity\Receptionist;
use App\Repository\GarageRepository;
use App\Repository\OwnerRepository;
use App\Repository\ReceptionistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
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
        private readonly RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private readonly RefreshTokenManagerInterface $refreshTokenManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly int $refreshTokenTtl,
        private readonly array $cookieConfig,
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

        // Handle garage-scoped authentication for Receptionist
        if ($user instanceof Receptionist) {
            // Receptionist MUST provide SIRET
            if (!isset($data['siret'])) {
                return $this->json(['error' => 'SIRET required for receptionist login'], 400);
            }

            // Find garage by SIRET
            $garage = $this->garageRepository->findOneBy(['siretNumber' => $data['siret']]);

            // Validate garage exists and receptionist works there
            if (!$garage || !$user->getGarages()->contains($garage)) {
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

        // Generate JWT access token
        $token = $this->jwtManager->create($user);

        // Revoke all existing refresh tokens for this user
        // This prevents database bloat and ensures only one active session
        $this->revokeExistingTokens($user->getUserIdentifier());

        // Generate new refresh token
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl(
            $user,
            $this->refreshTokenTtl
        );
        $this->refreshTokenManager->save($refreshToken);

        // Create response with access token
        $response = $this->json([
            'token' => $token,
            'refresh_token' => $refreshToken->getRefreshToken(),
        ]);

        // Set refresh token as HttpOnly cookie (using config from gesdinet_jwt_refresh_token.yaml)
        $cookie = Cookie::create('refresh_token')
            ->withValue($refreshToken->getRefreshToken())
            ->withExpires(new \DateTime(sprintf('+%d seconds', $this->refreshTokenTtl)))
            ->withPath($this->cookieConfig['path'] ?? '/')
            ->withDomain($this->cookieConfig['domain'])
            ->withSecure($this->cookieConfig['secure'] ?? false)
            ->withHttpOnly($this->cookieConfig['http_only'] ?? true)
            ->withSameSite($this->cookieConfig['same_site'] ?? Cookie::SAMESITE_LAX);

        $response->headers->setCookie($cookie);

        return $response;
    }

    /**
     * Revoke all existing refresh tokens for a user.
     * This ensures only one active session per user and prevents database bloat.
     */
    private function revokeExistingTokens(string $username): void
    {
        $connection = $this->entityManager->getConnection();

        // Delete all refresh tokens for this user
        $connection->executeStatement(
            'DELETE FROM refresh_tokens WHERE username = :username',
            ['username' => $username]
        );
    }
}
