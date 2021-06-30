<?php

namespace Permafrost\RayScan\Commands;

use Permafrost\RayScan\CodeScanner;
use Permafrost\RayScan\Printers\ConsoleResultPrinter;
use Permafrost\RayScan\Printers\ResultPrinter;
use Permafrost\RayScan\Support\Directory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScanCommand extends Command
{
    /** @var OutputInterface */
    protected $output;

    protected function configure(): void
    {
        $this->setName('scan')
            ->addArgument('path')
            ->setDescription('Scans a directory or filename for calls to ray() and rd().');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $path = $input->getArgument('path');
        $paths = $this->getPaths($path);

        $scanResults = $this->scanPaths(new CodeScanner(), $paths);

        $this->printResults(new ConsoleResultPrinter(), $scanResults);

        if (count($scanResults)) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function loadDirectoryFiles(string $path): array
    {
        $dir = new Directory(realpath($path));

        return $dir->load()->files();
    }

    protected function loadFile(string $filename): array
    {
        $filename = realpath($filename);
        $path = dirname($filename);

        $dir = (new Directory($path));

        return $dir->load()->only($filename);
    }

    protected function scanPaths(CodeScanner $scanner, array $paths): array
    {
        $scanResults = [];

        foreach($paths as $path) {
            $results = $scanner->scan($path, file_get_contents($path));

            if (!$results) {
                continue;
            }

            if (count($results->results)) {
                $scanResults[] = $results;
            }
        }

        return $scanResults;
    }

    protected function printResults(ResultPrinter $printer, array $scanResults): void
    {
        foreach ($scanResults as $scanResult) {
            foreach($scanResult->results as $result) {
                $printer->print($this->output, $result, true);
            }
        }
    }

    protected function getPaths(string $path): array
    {
        $paths = [];

        if (is_dir($path)) {
            $paths = $this->loadDirectoryFiles($path);
        }

        if (!is_dir($path) && is_file($path)) {
            $paths = $this->loadFile($path);
        }

        return $paths;
    }
}
