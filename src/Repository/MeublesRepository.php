<?php

namespace App\Repository;

use App\Entity\Meubles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Meubles>
 */
class MeublesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meubles::class);
    }
     public function recherche(string $critere): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.nomM LIKE :critere')
            ->orWhere('m.description LIKE :critere')
            ->setParameter('critere', '%'.$critere.'%')
            ->orderBy('m.nomM', 'ASC')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Meubles[] Returns an array of Meubles objects
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

    //    public function findOneBySomeField($value): ?Meubles
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
