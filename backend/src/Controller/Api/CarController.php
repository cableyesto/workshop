<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\CarService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CarController extends AbstractAuthenticatedController
{
    public function __construct(
        TokenStorageInterface $tokenStorage,
        JWTTokenManagerInterface $jwtManager,
        LoggerInterface $logger,
        private readonly CarService $carService,
    ) {
        parent::__construct($tokenStorage, $jwtManager, $logger);
    }

    #[Route('/api/cars/storage', name: 'api_cars_storage', methods: ['GET'])]
    public function storage(): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $cars = $this->carService->getStoredCars($garageId);

        return $this->json($cars);
    }

    #[Route('/api/cars/interventions', name: 'api_cars_interventions', methods: ['GET'])]
    public function interventions(): JsonResponse
    {
        $garageId = $this->getAuthenticatedGarageId();
        if ($garageId instanceof JsonResponse) {
            return $garageId;
        }

        $cars = $this->carService->getCarsWithInterventions($garageId);

        return $this->json($cars);
    }
}
