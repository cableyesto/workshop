<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CarRepository;
use Illuminate\Support\Collection;

final class CarService
{
    public function __construct(
        private readonly CarRepository $carRepository,
    ) {
    }

    /**
     * Get all stored cars for a specific garage
     *
     * @return array<int, array{id: int, manufacturer: string, model: string, licensePlate: string, color: string, client: array}>
     */
    public function getStoredCars(int $garageId): array
    {
        $cars = $this->carRepository->findStoredCars($garageId);

        return Collection::make($cars)
            ->map(fn($car) => [
                'id' => $car->getId(),
                'manufacturer' => $car->getManufacturer(),
                'model' => $car->getModel(),
                'licensePlate' => $car->getLicensePlate(),
                'color' => $car->getColor()->getName(),
                'client' => [
                    'id' => $car->getClient()->getId(),
                    'firstName' => $car->getClient()->getFirstName(),
                    'lastName' => $car->getClient()->getLastName(),
                    'email' => $car->getClient()->getEmail(),
                    'phone' => $car->getClient()->getPhoneNumber(),
                    'isClientCalledBack' => $car->getClient()->isClientCalledBack(),
                ],
            ])
            ->values()
            ->toArray();
    }
}
