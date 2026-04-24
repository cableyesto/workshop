<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\GarageRepository;
use App\Repository\MechanicRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Mechanic;

/**
 * Login controller for Mechanic authentication.
 * Handles PIN-based login scoped to a specific garage (by SIRET).
 */
final class MechanicLoginController extends AbstractController
{
    public function __construct(
        private readonly GarageRepository $garageRepository,
        private readonly MechanicRepository $mechanicRepository,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly LoggerInterface $securityLogger,
    ) {
    }

    #[Route('/api/garage/{siret}/mechanic/login', name: 'api_mechanic_login', methods: ['POST'], requirements: ['siret' => '\d{14}'])]
    public function login(string $siret, Request $request): JsonResponse
    {
        // Parse request
        $data = json_decode($request->getContent(), true);

        if (!isset($data['pin'])) {
            return $this->json(['error' => 'Missing PIN'], 400);
        }

        // Find garage by SIRET
        $garage = $this->garageRepository->findOneBy(['siretNumber' => $siret]);
        if (!$garage) {
            $this->securityLogger->warning('Mechanic login invalid garage', [
                'siret' => $siret,
                'ip' => $request->getClientIp(),
            ]);

            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        // Find all mechanics working at this garage
        $mechanics = $this->mechanicRepository->findByGarage($garage->getId());

        if (empty($mechanics)) {
            $this->securityLogger->warning('Mechanic login no mechanics at garage', [
                'siret' => $siret,
                'garage_id' => $garage->getId(),
                'ip' => $request->getClientIp(),
            ]);

            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        // Search for mechanic with matching PIN
        $authenticatedMechanic = null;
        foreach ($mechanics as $mechanic) {
            if ($mechanic->verifyPin($data['pin'])) {
                $authenticatedMechanic = $mechanic;
                break;
            }
        }

        if (!$authenticatedMechanic) {
            $this->securityLogger->warning('Mechanic login invalid PIN', [
                'siret' => $siret,
                'garage_id' => $garage->getId(),
                'ip' => $request->getClientIp(),
            ]);

            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        // Generate JWT token
        $token = $this->jwtManager->create($authenticatedMechanic);

        return $this->json(['token' => $token]);
    }
}
