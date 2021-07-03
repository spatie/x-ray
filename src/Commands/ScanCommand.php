<?php

namespace Permafrost\RayScan\Commands;

use Permafrost\RayScan\CodeScanner;
use Permafrost\RayScan\Configuration\Configuration;
use Permafrost\RayScan\Configuration\ConfigurationFactory;
use Permafrost\RayScan\Printers\ConsoleResultPrinter;
use Permafrost\RayScan\Printers\ResultPrinter;
use Permafrost\RayScan\Support\Directory;
use Permafrost\RayScan\Support\File;
use Permafrost\RayScan\Support\Progress;
use Permafrost\RayScan\Support\ProgressData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScanCommand extends Command
{
    /** @var OutputInterface */
    protected $output;

    /** @var Progress */
    protected $progress;

    /** @var Configuration */
    protected $config;

    protected function configure(): void
    {
        $this->setName('scan')
            ->addArgument('path')
            ->addOption('no-progress', 'P', InputOption::VALUE_NONE)
            ->addOption('no-snippets', 'N', InputOption::VALUE_NONE)
            ->setDescription('Scans a directory or filename for calls to ray() and rd().');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $io = new SymfonyStyle($input, $output);

        $this->config = ConfigurationFactory::create($input);
        $paths = $this->getPaths($this->config->path);

        $scanner = new CodeScanner();

        $this->progress = new Progress(count($paths), count($paths));

        if (! $this->config->hideProgress) {
            $io->progressStart(count($paths));

            $this->progress->withCallback(function (ProgressData $data) use ($io) {
                usleep(10000);
                $io->progressAdvance($data->position);
            });
        }

        $scanResults = $this->scanPaths($scanner, $paths);

        if (! $this->config->hideProgress) {
            $io->progressFinish();
        }

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

        return [$filename];
    }

    protected function scanPaths(CodeScanner $scanner, array $paths): array
    {
        $scanResults = [];

        foreach($paths as $path) {
            $results = $scanner->scan(new File($path));

            $this->progress->advance();

            if (!$results) {
                continue;
            }

            if ($results->hasErrors()) {
                // TODO: handle scan errors
            }

            if (! $results->hasErrors() && count($results->results)) {
                $scanResults[] = $results;
            }
        }

        return $scanResults;
    }

    protected function printResults(ResultPrinter $printer, array $scanResults): void
    {
        foreach ($scanResults as $scanResult) {
            foreach($scanResult->results as $result) {
                $printer->print($this->output, $result, true, !$this->config->hideSnippets);
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
