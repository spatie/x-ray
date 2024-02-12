<?php

namespace Spatie\XRay\Commands;

use InvalidArgumentException;
use Spatie\XRay\CodeScanner;
use Spatie\XRay\Configuration\Configuration;
use Spatie\XRay\Configuration\ConfigurationFactory;
use Spatie\XRay\Exceptions\MissingArgumentException;
use Spatie\XRay\Printers\ConsoleResultsPrinter;
use Spatie\XRay\Printers\MessagePrinter;
use Spatie\XRay\Printers\ResultsPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('clean')
            ->addArgument('path', InputArgument::REQUIRED)
            ->setDescription('Removes calls to ray(), ->ray(), rd() and Ray::*.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        $code = 0;
        passthru('php vendor/bin/rector process "'.$path.'" --dry-run --config ./remove-ray-rector.php', $code);

        return Command::SUCCESS;
    }
}
