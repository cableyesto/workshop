<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ManufacturerRepository;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ManufacturerController extends AbstractController
{
    public function __construct(
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/api/manufacturers', name: 'api_manufacturers_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $this->logger->info('Fetching all manufacturers');

        $manufacturers = $this->manufacturerRepository->findAll();

        $data = Collection::make($manufacturers)
            ->map(fn($manufacturer) => [
                'id' => $manufacturer->getId(),
                'name' => $manufacturer->getName(),
            ])
            ->sortBy('name')
            ->values()
            ->toArray();

        return $this->json($data);
    }
}
