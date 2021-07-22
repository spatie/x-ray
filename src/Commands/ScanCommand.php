<?php

namespace Permafrost\RayScan\Commands;

use Permafrost\PhpCodeSearch\Results\SearchResult;
use Permafrost\PhpCodeSearch\Support\File;
use Permafrost\RayScan\CodeScanner;
use Permafrost\RayScan\Configuration\Configuration;
use Permafrost\RayScan\Configuration\ConfigurationFactory;
use Permafrost\RayScan\Printers\ConsoleResultsPrinter;
use Permafrost\RayScan\Printers\ResultsPrinter;
use Permafrost\RayScan\Support\Progress;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class ScanCommand extends Command
{
    /** @var OutputInterface */
    protected $output;

    /** @var InputInterface */
    protected $input;

    /** @var Progress */
    protected $progress;

    /** @var Configuration */
    protected $config;

    /** @var ResultsPrinter */
    public $printer;

    /** @var CodeScanner */
    public $scanner;

    /** @var SymfonyStyle */
    public $style;

    /** @var array|SearchResult[] */
    public $scanResults = [];

    protected function configure(): void
    {
        $this->setName('scanFile')
            ->addArgument('path')
            ->addOption('no-progress', 'P', InputOption::VALUE_NONE)
            ->addOption('no-snippets', 'N', InputOption::VALUE_NONE)
            ->addOption('summary', 's', InputOption::VALUE_NONE)
            ->setDescription('Scans a directory or filename for calls to ray(), rd() and Ray::*.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this
            ->initializeProps($input, $output)
            ->initializePrinter()
            ->initializeScanner()
            ->initializeProgress()
            ->scanPaths()
            ->finalizeProgress()
            ->printResults();

        return count($this->scanResults) ? Command::FAILURE : Command::SUCCESS;
    }

    protected function initializeProps(InputInterface $input, OutputInterface $output): self
    {
        $this->output = $output;
        $this->input = $input;
        $this->style = new SymfonyStyle($input, $output);

        $this->config = ConfigurationFactory::create($this->input);
        $this->config->validate();

        return $this;
    }

    protected function initializePrinter(): self
    {
        $this->printer = new ConsoleResultsPrinter($this->output, $this->config);

        return $this;
    }

    protected function initializeProgress($paths = null): self
    {
        $paths = $paths ?? $this->scanner->paths();

        $this->progress = new Progress(count($paths));

        if (! $this->config->hideProgress) {
            $this->style->progressStart(count($paths));

            $this->progress->withCallback(function ($current, $total) {
                usleep(500);
                $this->style->progressAdvance();
            });
        }

        return $this;
    }

    protected function initializeScanner(): self
    {
        $this->scanner = new CodeScanner($this->config, $this->config->path);

        return $this;
    }

    protected function scanPaths(?CodeScanner $scanner = null, ?array $paths = null): self
    {
        $scanner = $scanner ?? $this->scanner;

        $this->scanResults = $scanner->scan($paths, function() {
            $this->progress->advance();
        });

        return $this;
    }

    protected function printResults(?ResultsPrinter $printer = null, ?array $scanResults = null): void
    {
        $printer = $printer ?? $this->printer;
        $scanResults = $scanResults ?? $this->scanResults;

        $printer->print($scanResults);
    }

    protected function finalizeProgress(): self
    {
        if (! $this->config->hideProgress) {
            $this->style->progressFinish();
        }

        return $this;
    }
}
