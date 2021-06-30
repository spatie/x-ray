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
        $path = $input->getArgument('path');
        $dir = new Directory(realpath($path));
        $paths = [];

        if (is_dir($path)) {
            $paths = $dir->load()->files();
        }

        if (!is_dir($path) && is_file($path)) {
            $filename = realpath($path);
            $path = dirname($filename);

            $dir = (new Directory($path));

            $paths = $dir->load()->only($filename);
        }

        $scanner = new CodeScanner();

        $resultCount = 0;

        foreach($paths as $path) {
            $results = $scanner->scan($path, file_get_contents($path));

            if (!$results) {
                continue;
            }

            $resultCount += count($results->results);

            if (count($results->results)) {
                foreach ($results->results as $result) {
                    ConsoleResultPrinter::print($output, $result);
                }
            }
        }

        if ($resultCount) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
