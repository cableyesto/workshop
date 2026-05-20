<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Car;
use App\Entity\Client;
use App\Enum\InterventionStatus;
use App\Repository\CarRepository;
use App\Repository\ClientRepository;
use App\Repository\ColorRepository;
use App\Repository\GarageRepository;
use Illuminate\Support\Collection;

final class CarService
{
    public function __construct(
        private readonly CarRepository $carRepository,
        private readonly ColorRepository $colorRepository,
        private readonly ClientRepository $clientRepository,
        private readonly GarageRepository $garageRepository,
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
                    'globalStatus' => $this->calculateGlobalStatus($car),
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
            ->filter(fn($car) => $this->calculateGlobalStatus($car) === 'Completed')
            ->map(fn($car) => [
                'id' => $car->getId(),
                'manufacturer' => $car->getManufacturer(),
                'model' => $car->getModel(),
                'licensePlate' => $car->getLicensePlate(),
                'color' => $car->getColor()->getName(),
                'client' => $this->mapClient($car->getClient()),
                'intervention' => [
                    'globalStatus' => 'Completed',
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
     * Calculate global intervention status for a car (aggregated across all interventions)
     * Returns the highest-priority status among all interventions
     */
    private function calculateGlobalStatus($car): string
    {
        $interventions = $car->getInterventions();

        if ($interventions->isEmpty()) {
            return 'Assigned';
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

        // Priority order: Active > Paused > Assigned > Completed
        if ($hasInProgress) {
            return 'Active';
        }
        if ($hasPaused) {
            return 'Paused';
        }
        if ($hasAssigned) {
            return 'Assigned';
        }

        return 'Completed';
    }

    /**
     * Update car storage status by ID
     */
    public function updateCarStorageById(
        int $carId,
        bool $isStored,
        int $garageId,
    ): void {
        $car = $this->carRepository->find($carId);

        if (!$car) {
            throw new \RuntimeException('Car not found');
        }

        // Verify car belongs to garage
        if ($car->getClient()->getGarage()->getId() !== $garageId) {
            throw new \RuntimeException('Car does not belong to this garage');
        }

        $car->setIsStored($isStored);

        // Reset client callback status when car is retrieved from storage
        if ($isStored === false) {
            $client = $car->getClient();
            $client->setIsClientCalledBack(false);
        }

        $this->carRepository->getEntityManager()->flush();
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

    /**
     * Update car information
     */
    public function updateCar(
        int $carId,
        string $manufacturer,
        string $model,
        string $licensePlate,
        string $colorName,
        ?int $registrationYear,
        ?int $registrationMonth,
        ?int $mileage,
    ): void {
        $car = $this->carRepository->find($carId);

        if (!$car) {
            throw new \RuntimeException('Car not found');
        }

        // Validate and fetch color entity
        $color = $this->colorRepository->findOneBy(['name' => $colorName]);

        if (!$color) {
            throw new \RuntimeException('Color not found. Please use a valid color from the database.');
        }

        $car
            ->setManufacturer($manufacturer)
            ->setModel($model)
            ->setLicensePlate($licensePlate)
            ->setColor($color)
            ->setRegistrationYear($registrationYear)
            ->setRegistrationMonth($registrationMonth)
            ->setMileage($mileage);

        $this->carRepository->getEntityManager()->flush();
    }

    /**
     * Create a new car with a new client
     */
    public function createCarWithClient(
        int $garageId,
        string $clientFirstName,
        string $clientLastName,
        ?string $clientEmail,
        string $clientPhone,
        string $carManufacturer,
        string $carModel,
        string $carLicensePlate,
        string $carColorName,
        ?int $carRegistrationYear,
        ?int $carRegistrationMonth,
        ?int $carMileage,
    ): array {
        $em = $this->carRepository->getEntityManager();

        try {
            $em->beginTransaction();

            // Validate and fetch garage entity
            $garage = $this->garageRepository->find($garageId);
            if (!$garage) {
                throw new \RuntimeException('Garage not found');
            }

            // Validate and fetch color entity
            $color = $this->colorRepository->findOneBy(['name' => $carColorName]);
            if (!$color) {
                throw new \RuntimeException('Color not found. Please use a valid color from the database.');
            }

            // Create client
            $client = new Client();
            $client
                ->setFirstName($clientFirstName)
                ->setLastName($clientLastName)
                ->setEmail($clientEmail)
                ->setPhoneNumber($clientPhone)
                ->setIsClientCalledBack(false)
                ->setGarage($garage);

            $em->persist($client);

            // Create car
            $car = new Car();
            $car
                ->setManufacturer($carManufacturer)
                ->setModel($carModel)
                ->setLicensePlate($carLicensePlate)
                ->setColor($color)
                ->setRegistrationYear($carRegistrationYear)
                ->setRegistrationMonth($carRegistrationMonth)
                ->setMileage($carMileage)
                ->setIsStored(false)
                ->setClient($client);

            $em->persist($car);
            $em->flush();
            $em->commit();

            // Return created car data
            return [
                'id' => $car->getId(),
                'manufacturer' => $car->getManufacturer(),
                'model' => $car->getModel(),
                'licensePlate' => $car->getLicensePlate(),
                'color' => $car->getColor()->getName(),
                'registrationYear' => $car->getRegistrationYear(),
                'registrationMonth' => $car->getRegistrationMonth(),
                'mileage' => $car->getMileage(),
                'isStored' => $car->isStored(),
                'client' => [
                    'id' => $client->getId(),
                    'firstName' => $client->getFirstName(),
                    'lastName' => $client->getLastName(),
                    'email' => $client->getEmail(),
                    'phone' => $client->getPhoneNumber(),
                    'isClientCalledBack' => $client->isClientCalledBack(),
                ],
            ];
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }
    }
}
