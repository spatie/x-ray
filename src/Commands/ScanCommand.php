<?php

namespace Permafrost\RayScan\Commands;

use Permafrost\PhpCodeSearch\Results\SearchResult;
use Permafrost\PhpCodeSearch\Support\File;
use Permafrost\RayScan\CodeScanner;
use Permafrost\RayScan\Concerns\HasPaths;
use Permafrost\RayScan\Concerns\HasProgress;
use Permafrost\RayScan\Configuration\Configuration;
use Permafrost\RayScan\Configuration\ConfigurationFactory;
use Permafrost\RayScan\Printers\ConsoleResultsPrinter;
use Permafrost\RayScan\Printers\ResultsPrinter;
use Permafrost\RayScan\Support\Directory;
use Permafrost\RayScan\Support\Progress;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScanCommand extends Command
{
    use HasPaths;
    use HasProgress;

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

    /** @var array|string[] */
    public $paths = [];

    /** @var array|SearchResult[] */
    public $scanResults = [];

    protected function configure(): void
    {
        $this->setName('scan')
            ->addArgument('path')
            ->addOption('no-progress', 'P', InputOption::VALUE_NONE)
            ->addOption('no-snippets', 'N', InputOption::VALUE_NONE)
            ->setDescription('Scans a directory or filename for calls to ray(), rd() and Ray::*.');
    }

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->printer = new ConsoleResultsPrinter();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeProps($input, $output)
            ->initializeConfig()
            ->initializeScanner()
            ->initializePaths()
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

        return $this;
    }

    protected function initializeConfig(): self
    {
        $this->config = ConfigurationFactory::create($this->input);
        $this->config->validate();

        return $this;
    }

    protected function initializeScanner(): self
    {
        $this->scanner = new CodeScanner($this->config);

        return $this;
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

    protected function scanPaths(?CodeScanner $scanner = null, ?array $paths = null): self
    {
        $scanner = $scanner ?? $this->scanner;
        $paths = $paths ?? $this->paths;

        $this->scanResults = [];

        foreach($paths as $path) {
            if (in_array($path, $this->config->ignorePaths, true)) {
                continue;
            }

            if (in_array(basename($path), $this->config->ignorePaths, true)) {
                continue;
            }

            foreach($this->config->ignorePaths as $ignoreFile) {
                $ignoreFile = str_replace(['*', '?'], ['.*', '.'], $ignoreFile);

                if (preg_match('~' . $ignoreFile . '~', $path) === 1) {
                    continue 2;
                }
            }

            $results = $scanner->scan(new File($path));

            $this->progress->advance();

            if (!$results) {
                continue;
            }

            if ($results->hasErrors()) {
                // TODO: handle scan errors
            }

            if (! $results->hasErrors() && count($results->results)) {
                $this->scanResults[] = $results;
            }
        }

        return $this;
    }

    protected function printResults(?ResultsPrinter $printer = null, ?array $scanResults = null): void
    {
        $printer = $printer ?? $this->printer;
        $scanResults = $scanResults ?? $this->scanResults;

        $printer->print($this->output, $scanResults, $this->config);
    }
}
