<?php

namespace App\Repository;

use App\Entity\BaseRoutes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BaseRoutes|null find($id, $lockMode = null, $lockVersion = null)
 * @method BaseRoutes|null findOneBy(array $criteria, array $orderBy = null)
 * @method BaseRoutes[]    findAll()
 * @method BaseRoutes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BaseRoutesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaseRoutes::class);
    }

    // /**
    //  * @return BaseRoutes[] Returns an array of BaseRoutes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BaseRoutes
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
