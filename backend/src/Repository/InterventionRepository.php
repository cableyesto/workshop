<?php

namespace App\Repository;

use App\Entity\Intervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    /**
     * Find active intervention for a specific mechanic and car (by license plate)
     */
    public function findActiveByMechanicAndLicensePlate(
        int $mechanicId,
        string $licensePlate,
        int $garageId
    ): ?Intervention {
        return $this->createQueryBuilder('i')
            ->select('i', 'car', 'mechanics', 'client')
            ->join('i.car', 'car')
            ->join('car.client', 'client')
            ->join('i.mechanics', 'mechanics')
            ->join('mechanics.garages', 'mechanicGarages')
            ->where('car.licensePlate = :licensePlate')
            ->andWhere('client.garage = :garageId')
            ->andWhere('mechanics.id = :mechanicId')
            ->andWhere('mechanicGarages.id = :garageId')
            ->andWhere('i.status IN (:activeStatuses)')
            ->setParameter('licensePlate', $licensePlate)
            ->setParameter('garageId', $garageId)
            ->setParameter('mechanicId', $mechanicId)
            ->setParameter('activeStatuses', ['Assigned', 'In Progress', 'Paused'])
            ->orderBy('i.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Intervention[] Returns an array of Intervention objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Intervention
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
