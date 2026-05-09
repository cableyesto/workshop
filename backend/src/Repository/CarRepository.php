<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Car;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Car>
 */
final class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    /**
     * Find all stored cars for a specific garage
     *
     * @return Car[]
     */
    public function findStoredCars(int $garageId): array
    {
        return $this->createQueryBuilder('car')
            ->select('car', 'color', 'client')
            ->join('car.color', 'color')
            ->join('car.client', 'client')
            ->join('car.interventions', 'intervention')
            ->join('intervention.mechanics', 'mechanic')
            ->join('mechanic.garages', 'garage')
            ->where('car.isStored = :isStored')
            ->andWhere('garage.id = :garageId')
            ->setParameter('isStored', true)
            ->setParameter('garageId', $garageId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all cars with interventions for a specific garage
     *
     * @return Car[]
     */
    public function findCarsWithInterventions(int $garageId): array
    {
        return $this->createQueryBuilder('car')
            ->select('car', 'color', 'client', 'interventions')
            ->join('car.color', 'color')
            ->join('car.client', 'client')
            ->join('car.interventions', 'interventions')
            ->join('interventions.mechanics', 'mechanic')
            ->join('mechanic.garages', 'garage')
            ->where('garage.id = :garageId')
            ->setParameter('garageId', $garageId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all stored cars with interventions for a specific garage
     *
     * @return Car[]
     */
    public function findCarsForRestitution(int $garageId): array
    {
        return $this->createQueryBuilder('car')
            ->select('car', 'color', 'client', 'interventions')
            ->join('car.color', 'color')
            ->join('car.client', 'client')
            ->join('car.interventions', 'interventions')
            ->join('interventions.mechanics', 'mechanic')
            ->join('mechanic.garages', 'garage')
            ->where('garage.id = :garageId')
            ->andWhere('car.isStored = :isStored')
            ->setParameter('garageId', $garageId)
            ->setParameter('isStored', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find car by license plate for a specific garage
     */
    public function findByLicensePlateForGarage(string $licensePlate, int $garageId): ?Car
    {
        return $this->createQueryBuilder('car')
            ->join('car.client', 'client')
            ->join('car.interventions', 'interventions')
            ->join('interventions.mechanics', 'mechanic')
            ->join('mechanic.garages', 'garage')
            ->where('car.licensePlate = :licensePlate')
            ->andWhere('garage.id = :garageId')
            ->setParameter('licensePlate', $licensePlate)
            ->setParameter('garageId', $garageId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
