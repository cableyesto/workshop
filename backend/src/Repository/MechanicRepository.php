<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Mechanic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mechanic>
 */
class MechanicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mechanic::class);
    }

    /**
     * Find all mechanics working at a specific garage.
     * More efficient than loading all employees and filtering.
     *
     * @return Mechanic[]
     */
    public function findByGarage(int $garageId): array
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.garages', 'g')
            ->where('g.id = :garageId')
            ->setParameter('garageId', $garageId)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Mechanic[] Returns an array of Mechanic objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Mechanic
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
