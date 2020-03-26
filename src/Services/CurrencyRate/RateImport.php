<?php

namespace App\Services\CurrencyRate;

use App\Entity\Currency;
use App\Entity\Rate;
use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * Получение данных из внешнего источника
 *
 * Class RateImport
 */
class RateImport
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var RateSourceInterface
     */
    private RateSourceInterface $cbr;

    /**
     * RateImport constructor.
     * @param EntityManagerInterface $entityManager
     * @param RateSourceInterface $cbr
     */
    public function __construct(EntityManagerInterface $entityManager, RateSourceInterface $cbr) {
        $this->entityManager = $entityManager;
        $this->cbr = $cbr;
    }

    /**
     * Получение данных из внешнего источника
     *
     * @param DateTimeInterface|null $from
     * @param DateTimeInterface|null $to
     * @return array
     * @throws RateSourceException
     */
    public function import(?DateTimeInterface $from=null, ?DateTimeInterface $to=null): array  {
        if (null === $from) {
            $from = new \DateTime();
        }
        if (null === $to) {
            $to = new \DateTime();
        }
        if ($from > $to) {
            throw new RateSourceException('Ошибка. Дата начала больше даты окончания');
        }

        $currencyRepository = $this->entityManager->getRepository(Currency::class);
        $currencies = [];
        /** @var Currency $currency */
        foreach ($currencyRepository->findAll() as $currency) {
            $currencies[$currency->getCode()] = $currency;
        }

        $this->entityManager->beginTransaction();

        // Просто удалим существующие данные, чтобы не было дублей
        $qb = $this->entityManager->createQueryBuilder()
            ->delete(Rate::class, 'r')
            ->where('r.date BETWEEN :from AND :to')
            ->setParameter('from', $from,  \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)
        ;
        $query = $qb->getQuery();
        $query->execute();

        $return = [
            'count' => 0
        ];

        while ($from <= $to) {
            $rates = $this->cbr->getDay($from);
            foreach ($rates as $item) {
                $dt = clone $from;
                $rate = (new Rate())
                    ->setRate(str_replace(',', '.', $item['rate']))
                    ->setDate($dt)
                    ->setCurrency($currencies[$item['code']]);
                $this->entityManager->persist($rate);

                $return['count']++;
            }
            $from->modify('+1day');
        }
        $this->entityManager->flush();
        $this->entityManager->commit();

        return $return;
    }



}