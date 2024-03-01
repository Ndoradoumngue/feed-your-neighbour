<?php

namespace App\Repository;

use App\Entity\PositionGPS;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PositionGPS>
 *
 * @method PositionGPS|null find($id, $lockMode = null, $lockVersion = null)
 * @method PositionGPS|null findOneBy(array $criteria, array $orderBy = null)
 * @method PositionGPS[]    findAll()
 * @method PositionGPS[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PositionGPSRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PositionGPS::class);
    }

//    /**
//     * @return PositionGPS[] Returns an array of PositionGPS objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PositionGPS
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
