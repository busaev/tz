<?php

namespace App\Repository;

use App\Entity\Currency;
use App\Entity\Rate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @method Rate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rate[]    findAll()
 * @method Rate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RateRepository extends ServiceEntityRepository
{
    public const PER_PAGE_100 = 100;
    public const PER_PAGE_ALL = 999999;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rate::class);
    }

    /**
     * Поиск курсов по параметрам
     *
     * Сделал так для примера. QueryBuilder ом редко пользуюсь
     * В основном нужны данные которые проще и быстрее собрать на sql
     *
     * @param Currency|null $currency
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @param int $currentPage
     * @param int $perPage
     * @param string $sort
     * @param string $dir
     * @return array
     */
    public function findByDateAndCurrency(?Currency $currency = null,
                                          ?\DateTime $from = null,
                                          ?\DateTime $to = null,
                                          int $perPage = self::PER_PAGE_100,
                                          int $currentPage = 1,
                                          string $sort = 'date',
                                          string $dir = 'asc'
        ): array {

        $qb = $this->createQueryBuilder('r');
        if (null !== $currency) {
            $qb
                ->andWhere('r.currency = :currency')
                ->setParameter('currency', $currency);
        }
        if (null !== $from) {
            $qb
                ->andWhere('r.date >= :from')
                ->setParameter('from', $from,  \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);
        }

        if (null !== $to) {
            $to->setTime(23, 59, 59);
            $qb
                ->andWhere('r.date <= :to')
                ->setParameter('to', $to,  \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);
        }

        if (!in_array($sort, ['date', 'rate'])) {
            $sort = 'date';
        }
        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $qb
            ->orderBy('r.'.$sort, $dir)
            ->setFirstResult($perPage * ($currentPage-1))
            ->setMaxResults($perPage);

        $paginator  = new Paginator($qb->getQuery());

        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $perPage);

        return [
            'paginator' => $paginator,
            'totalItems' => $totalItems,
            'pagesCount' => $pagesCount,
        ];
    }
}
