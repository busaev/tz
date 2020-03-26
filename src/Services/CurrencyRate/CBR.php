<?php

namespace App\Services\CurrencyRate;

use App\Entity\Currency;
use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use SimpleXMLElement;

/**
 * Получение курсов валют с сайта ЦБР
 *
 * Class CBR
 */
class CBR implements RateSourceInterface
{
    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * @var string
     */
    private string $urlDaily;

    /**
     * @var string
     */
    private string $urlDynamic;

    /**
     * CBR constructor.
     * @param EntityManager $entityManager
     * @param string $urlDaily
     * @param string $urlDynamic
     */
    public function __construct(EntityManager $entityManager, string $urlDaily, string $urlDynamic) {
        $this->entityManager = $entityManager;
        $this->urlDaily = $urlDaily;
        $this->urlDynamic = $urlDynamic;
    }

    /**
     * Получить данные по поределённой валюте
     *
     * @param string $code ISO код валюты
     * @param DateTimeInterface|null $from
     * @param DateTimeInterface|null $to
     * @return array
     * @throws RateSourceException
     */
    public function getCurrency(string $code, ?DateTimeInterface $from=null, ?DateTimeInterface $to=null): array {
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
        $currency = $currencyRepository->findOneBy(['code'=>$code]);
        if (null === $currency) {
            throw new RateSourceException(sprintf(
                'Валюта с кодом %s не найдена',
                $code
            ));
        }

        $url = sprintf(
            '%s?date_req1=%s&date_req2=%s&VAL_NM_RQ=%s',
            $this->urlDynamic,
            $from->format('d/m/Y'),
            $to->format('d/m/Y'),
            $currency->getCodeCbr()
        );

        $resp = $this->request($url);

        /** @var SimpleXMLElement $xml */
        $xml = simplexml_load_string($resp);
        if (!$xml) {
            throw new RateSourceException('Не удалось разобрать ответ ЦБР');
        }

        $return = [];
        /** @var SimpleXMLElement $record */
        foreach ($xml->Record as $record) {
            $return[] = [
                'code' => $code,
                'date' => (string)$record['Date'],
                'rate' => (string)$record->Value
            ];
        }
        return $return;
    }

    /**
     * @param DateTimeInterface|null $from
     * @return array
     * @throws RateSourceException
     */
    public function getDay(?DateTimeInterface $from=null): array {
        if (null === $from) {
            $from = new \DateTime();
        }

        $currencyRepository = $this->entityManager->getRepository(Currency::class);
        $currencies = [];
        /** @var Currency $currency */
        foreach ($currencyRepository->findAll() as $currency) {
            $currencies[$currency->getCodeCbr()] = $currency;
        }

        $url = sprintf(
            '%s?date_req=%s&VAL_NM_RQ=%s',
            $this->urlDaily,
            $from->format('d/m/Y'),
            $currency->getCodeCbr()
        );

        $resp = $this->request($url);

        /** @var SimpleXMLElement $xml */
        $xml = simplexml_load_string($resp);
        if (!$xml) {
            throw new RateSourceException('Не удалось разобрать ответ ЦБР');
        }

        $return = [];
        /** @var SimpleXMLElement $valute */
        foreach ($xml->Valute as $valute) {
            $cbrCode = (string)$valute['ID'];
            // нам такие не нужны
            if (!isset($currencies[$cbrCode])) {
                continue;
            }
            /** @var Currency $currency */
            $currency = $currencies[$cbrCode];

            $return[] = [
                'code' => $currency->getCode(),
                'date' => $from->format('d.m.Y'),
                'rate' => (string)$valute->Value
            ];
        }
        return $return;
    }

    /**
     * Тут можно было бы сделать отдельную службу для внешних запросов
     * и использовать какую-нибудь библиотеку, например, Guzzle
     *
     * @param string $url
     * @return bool|string
     * @throws RateSourceException
     */
    private function request(string $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $resp = curl_exec($ch);

        if (200 !== curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            throw new RateSourceException(sprintf(
                'Ну удалось получить ответ с сайта ЦБР. Url - %s, HttpCode - %d', $url, curl_getinfo($ch, CURLINFO_HTTP_CODE)
            ));
        }

        curl_close ($ch);

        return $resp;
    }


}