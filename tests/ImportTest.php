<?php

namespace App\Tests;

use App\Services\CurrencyRate\RateImport;
use App\Services\CurrencyRate\RateSourceException;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImportTest extends KernelTestCase
{

    public function testException()
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();

        $this->expectException(RateSourceException::class);
        $container->get(RateImport::class)->import(new \DateTime(), (new \DateTime())->modify('-1day'));

        $this->assertTrue(true);
    }
}
