<?php

namespace App\Repository;

use App\Entity\Financier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Financier>
 *
 * @method Financier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Financier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Financier[]    findAll()
 * @method Financier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FinancierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Financier::class);
    }

//    /**
//     * @return Financier[] Returns an array of Financier objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Financier
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
