<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Génère le prochain numéro de facture au format AAAA-NNNN (ex: 2026-0001).
     * Réinitialise le compteur chaque année.
     */
    public function nextInvoiceNumber(?string $year = null): string
    {
        $year ??= (new \DateTimeImmutable())->format('Y');
        $pattern = $year . '-%';

        $last = $this->createQueryBuilder('o')
            ->select('o.invoiceNumber')
            ->where('o.invoiceNumber LIKE :pattern')
            ->setParameter('pattern', $pattern)
            ->orderBy('o.invoiceNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $next = 1;
        if ($last !== null && isset($last['invoiceNumber'])) {
            $parts = explode('-', $last['invoiceNumber']);
            $next = (int) end($parts) + 1;
        }

        return sprintf('%s-%04d', $year, $next);
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
