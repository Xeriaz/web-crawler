<?php

namespace App\Repository;

use App\Constant\RouteStates;
use App\Entity\Routes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Routes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Routes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Routes[]    findAll()
 * @method Routes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoutesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Routes::class);
    }

    public function findPendingRoutesByBaseUrl(string $baseLink)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.route LIKE :baseLink')
            ->andWhere('r.state = :state')
            ->setParameter('baseLink', $baseLink . '%')
            ->setParameter('state', RouteStates::PENDING)
            ->getQuery()
            ->getResult();
    }
}
