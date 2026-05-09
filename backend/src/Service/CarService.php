<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Car;
use App\Entity\Client;
use App\Enum\InterventionStatus;
use App\Repository\CarRepository;
use App\Repository\ClientRepository;
use App\Repository\ColorRepository;
use Illuminate\Support\Collection;

final class CarService
{
    public function __construct(
        private readonly CarRepository $carRepository,
        private readonly ColorRepository $colorRepository,
        private readonly ClientRepository $clientRepository,
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
                ->setIsClientCalledBack(false);

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
