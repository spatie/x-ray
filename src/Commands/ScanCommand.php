<?php

namespace Permafrost\RayScan\Commands;

use Permafrost\RayScan\CodeScanner;
use Permafrost\RayScan\Printers\ConsoleResultPrinter;
use Permafrost\RayScan\Support\Directory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScanCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('scan')
            ->addArgument('path')
            ->setDescription('Scans a directory or file for calls to ray() and rd().');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('path');

        if (! is_dir($filename)) {
            $output->writeln('<fg=yellow>Error: Please specify a valid directory path to scan.</>');

            return Command::INVALID;
        }

        $scanner = new CodeScanner();
        $dir = new Directory($filename);

        foreach($dir->files() as $filename) {
            $results = $scanner->scan($filename, file_get_contents($filename));

            if (!$results) {
                return Command::FAILURE;
            }

            if (count($results->results)) {
                foreach ($results->results as $result) {
                    ConsoleResultPrinter::print($output, $result);
                }

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
