<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $currencies = [
            'AUD' => ['name' => 'Австралийский доллар', 'code' => 'R01010'],
            'GBP' => ['name' => 'Фунт стерлингов Соединенного королевства', 'code' => 'R01035'],
            'BYR' => ['name' => 'Белорусских рублей', 'code' => 'R01035'],
            'DKK' => ['name' => 'Датских крон', 'code' => 'R01215'],
            'USD' => ['name' => 'Доллар США', 'code' => 'R01235'],
            'EUR' => ['name' => 'Евро', 'code' => 'R01239'],
            'ISK' => ['name' => 'Исландских крон', 'code' => 'R01310'],
            'CAD' => ['name' => 'Канадский доллар', 'code' => 'R01350'],
            'NOK' => ['name' => 'Норвежских крон', 'code' => 'R01535'],
        ];

        foreach ($currencies as $currencyCode => $currencyData) {
            $currency = new Currency();
            $currency
                ->setCode($currencyCode)
                ->setName($currencyData['name'])
                ->setCodeCbr($currencyData['code'])
            ;

            $manager->persist($currency);

        }
        $manager->flush();
    }
}
