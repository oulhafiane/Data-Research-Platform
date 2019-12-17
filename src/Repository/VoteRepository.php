<?php

namespace App\Repository;

use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Vote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vote[]    findAll()
 * @method Vote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    // /**
    //  * @return Vote[] Returns an array of Vote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Vote
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getCountGood($problematic)
    {
        return $this->createQueryBuilder('v')
            ->select('count(v.id)')
            ->where('v.problematic = ?1')
		    ->setParameter(1, $problematic)
            ->andWhere('v.good = 1')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getCountNotGood($problematic)
    {
        return $this->createQueryBuilder('v')
            ->select('count(v.id)')
            ->where('v.problematic = ?1')
		    ->setParameter(1, $problematic)
            ->andWhere('v.good = 0')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
