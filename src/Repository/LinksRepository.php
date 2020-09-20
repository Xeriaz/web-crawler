<?php

namespace App\Repository;

use App\Constant\LinksStates;
use App\Entity\Links;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Links|null find($id, $lockMode = null, $lockVersion = null)
 * @method Links|null findOneBy(array $criteria, array $orderBy = null)
 * @method Links[]    findAll()
 * @method Links[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Links::class);
    }

    public function findPendingLinksByBaseUrl(string $baseLink)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.link LIKE :baseLink')
            ->andWhere('r.state = :state')
            ->setParameter('baseLink', $baseLink . '%')
            ->setParameter('state', LinksStates::PENDING)
            ->getQuery()
            ->getResult();
    }
}
