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
use Symfony\Component\Console\Output\AnsiColorMode;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class CleanCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('clean')
            ->addArgument('path', InputArgument::REQUIRED)
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Don\'t actually change any files')
            ->setDescription('Removes calls to ray(), ->ray(), rd() and Ray::*.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        $dryRun = $input->getOption('dry-run');

        $configFn = file_exists('./remove-ray-rector.php')
            ? './remove-ray-rector.php'
            : './vendor/spatie/x-ray/remove-ray-rector.php';

        $flags = implode(' ', array_filter([
            $dryRun ? '--dry-run' : false,
            '--config '.$configFn,
        ]));

        passthru('php vendor/bin/rector process "'.$path.'" '.$flags.' >&2', $code);

        return $code === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
