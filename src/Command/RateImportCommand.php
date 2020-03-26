<?php

namespace App\Command;

use App\Entity\Currency;
use App\Entity\Rate;
use App\Repository\RateRepository;
use App\Services\CurrencyRate\CBR;
use App\Services\CurrencyRate\RateImport;
use App\Services\CurrencyRate\RateSourceException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarDumper\VarDumper;

class RateImportCommand extends Command
{
    protected static $defaultName = 'app:rate:import';
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var RateImport
     */
    private RateImport $rateImport;

    public function __construct(EntityManagerInterface $entityManager, RateImport $rateImport)
    {
        $this->entityManager = $entityManager;
        $this->rateImport = $rateImport;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Сохранить курсы валют за период, по-умолчанию - за сегодня')
            ->addArgument('from', InputArgument::OPTIONAL, 'Начало периода')
            ->addArgument('to', InputArgument::OPTIONAL, 'Окончание периода')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fromArg = $input->getArgument('from');
        $toArg = $input->getArgument('to');

        $from = new \DateTime();
        $to = new \DateTime();

        if ($fromArg) {
            try {
                $from = new \DateTime($fromArg);
            } catch (\Exception $e) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Не удалось разобрать дату. Полученное значение - %s, необходимый формат даты: dd.mm.yyyy',
                        $fromArg
                    )
                );
            }
        }
        if ($toArg) {
            try {
                $to = new \DateTime($toArg);
            } catch (\Exception $e) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Не удалось разобрать дату. Полученное значение - %s, необходимый формат даты: dd.mm.yyyy',
                        $toArg
                    )
                );
            }
        }

        try {
            $this->rateImport->import($from, $to);
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }

        $io->success('Готово.');

        return 0;
    }
}
