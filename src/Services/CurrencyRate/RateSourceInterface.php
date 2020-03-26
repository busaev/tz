<?php

namespace App\Services\CurrencyRate;

use App\Entity\Currency;
use DateTimeInterface;

/**
 * Интерфейс для взаимодействия с источниками курсов
 *
 * Interface RateSourceInterface
 * @package App\Services\CurrencyRate
 */
interface RateSourceInterface
{
    /**
     * Получить данные для определённой валюты за период
     *
     * @param string $code ISO код валюты https://www.iso.org/ru/iso-4217-currency-codes.html
     * @param DateTimeInterface|null $from начало периода
     * @param DateTimeInterface|null $to конец периода
     * @return array|null
     */
    public function getCurrency(string $code, ?\DateTimeInterface $from=null, ?\DateTimeInterface $to=null): ?array;

    /**
     * Получить сводку по всем валютам за указанный день.
     *
     * ПО-умолчанию за сегодня
     * @param DateTimeInterface|null $from
     * @return mixed
     */
    public function getDay(?\DateTimeInterface $from=null);
}