<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\InterventionStatus;
use App\Repository\CarRepository;
use Illuminate\Support\Collection;

final class CarService
{
    public function __construct(
        private readonly CarRepository $carRepository,
    ) {
    }

    /**
     * Get all cars for a specific garage
     */
    public function getAllCars(int $garageId): array
    {
        $cars = $this->carRepository->findAllForGarage($garageId);

        return Collection::make($cars)
            ->map(fn($car) => [
                'id' => $car->getId(),
                'manufacturer' => $car->getManufacturer(),
                'model' => $car->getModel(),
                'licensePlate' => $car->getLicensePlate(),
                'color' => $car->getColor()->getName(),
                'registrationYear' => $car->getRegistrationYear(),
                'registrationMonth' => $car->getRegistrationMonth(),
                'mileage' => $car->getMileage(),
                'isStored' => $car->isStored(),
                'client' => $this->mapClient($car->getClient()),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get all stored cars for a specific garage
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
                'client' => $this->mapClient($car->getClient()),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get all cars with interventions for a specific garage
     */
    public function getCarsWithInterventions(int $garageId): array
    {
        $cars = $this->carRepository->findCarsWithInterventions($garageId);

        return Collection::make($cars)
            ->map(fn($car) => [
                'id' => $car->getId(),
                'manufacturer' => $car->getManufacturer(),
                'model' => $car->getModel(),
                'licensePlate' => $car->getLicensePlate(),
                'color' => $car->getColor()->getName(),
                'client' => $this->mapClient($car->getClient()),
                'intervention' => [
                    'status' => $this->calculateGlobalStatus($car),
                ],
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get all cars ready for restitution for a specific garage
     */
    public function getCarsForRestitution(int $garageId): array
    {
        $cars = $this->carRepository->findCarsForRestitution($garageId);

        return Collection::make($cars)
            ->filter(fn($car) => $this->calculateGlobalStatus($car) === 'terminee')
            ->map(fn($car) => [
                'id' => $car->getId(),
                'manufacturer' => $car->getManufacturer(),
                'model' => $car->getModel(),
                'licensePlate' => $car->getLicensePlate(),
                'color' => $car->getColor()->getName(),
                'client' => $this->mapClient($car->getClient()),
                'intervention' => [
                    'status' => 'terminee',
                ],
            ])
            ->values()
            ->toArray();
    }

    /**
     * Map client entity to array
     */
    private function mapClient($client): array
    {
        return [
            'id' => $client->getId(),
            'firstName' => $client->getFirstName(),
            'lastName' => $client->getLastName(),
            'email' => $client->getEmail(), // Nullable
            'phone' => $client->getPhoneNumber(),
            'isClientCalledBack' => $client->isClientCalledBack(),
        ];
    }

    /**
     * Calculate global intervention status for a car
     */
    private function calculateGlobalStatus($car): string
    {
        $interventions = $car->getInterventions();

        if ($interventions->isEmpty()) {
            return 'affectee';
        }

        $hasInProgress = false;
        $hasPaused = false;
        $hasAssigned = false;

        foreach ($interventions as $intervention) {
            $status = $intervention->getStatus();

            if ($status === InterventionStatus::InProgress) {
                $hasInProgress = true;
            } elseif ($status === InterventionStatus::Paused) {
                $hasPaused = true;
            } elseif ($status === InterventionStatus::Assigned) {
                $hasAssigned = true;
            }
        }

        if ($hasInProgress) {
            return 'en_cours';
        }
        if ($hasPaused) {
            return 'en_pause';
        }
        if ($hasAssigned) {
            return 'affectee';
        }

        return 'terminee';
    }

    /**
     * Update car storage status by license plate
     */
    public function updateCarStorageByLicensePlate(
        string $licensePlate,
        bool $isStored,
        int $garageId,
    ): void {
        $car = $this->carRepository->findByLicensePlateForGarage($licensePlate, $garageId);

        if (!$car) {
            throw new \RuntimeException('Aucune fiche trouvée pour cette plaque d\'immatriculation');
        }

        $car->setIsStored($isStored);
        $this->carRepository->getEntityManager()->flush();
    }
}
