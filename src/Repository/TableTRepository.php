<?php

namespace App\Repository;

use App\Entity\TableT;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TableT|null find($id, $lockMode = null, $lockVersion = null)
 * @method TableT|null findOneBy(array $criteria, array $orderBy = null)
 * @method TableT[]    findAll()
 * @method TableT[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TableTRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TableT::class);
    }

    // /**
    //  * @return TableT[] Returns an array of TableT objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TableT
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
