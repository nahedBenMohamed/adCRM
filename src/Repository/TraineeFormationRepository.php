<?php
namespace App\Repository;

use App\Entity\TraineeFormation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TraineeFormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TraineeFormation::class);
    }

    public function deleteTrainee($idFormation, $idTrainee)
    {
        $query = $this->getUserRepository()
            ->createQueryBuilder('du')
            ->delete('du')
            ->where('du.formation_id = :idFormation')
            ->andWhere('du.trainee_id = :idTrainee')
            ->setParameter("idFormation", $idFormation)
            ->setParameter("idTrainee", $idTrainee)
            ->getQuery()
            ->execute();

        return $query;
    }

    public function findAllTraineeByDate()
    {
        $query = $this->createQueryBuilder('du')
            ->select('du.dateConvocation, COUNT(du.id) AS total')
            ->where('du.sendConvocation = 1')
            ->orderBy('du.dateConvocation', 'ASC')
            ->groupBy('du.dateConvocation')
            ->getQuery()
            ->execute();
        return $query;
    }

}
