<?php

namespace Spatie\RayScan\Commands;

use Permafrost\PhpCodeSearch\Results\SearchResult;
use Spatie\RayScan\CodeScanner;
use Spatie\RayScan\Configuration\Configuration;
use Spatie\RayScan\Configuration\ConfigurationFactory;
use Spatie\RayScan\Exceptions\MissingArgumentException;
use Spatie\RayScan\Printers\ConsoleResultsPrinter;
use Spatie\RayScan\Printers\MessagePrinter;
use Spatie\RayScan\Printers\ResultsPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScanCommand extends Command
{
    /** @var Configuration */
    protected $config;

    /** @var ResultsPrinter */
    public $printer;

    /** @var OutputInterface */
    public $output;

    /** @var CodeScanner */
    public $scanner;

    /** @var array|SearchResult[] */
    public $scanResults = [];

    /** @var SymfonyStyle */
    public $style;

    protected function configure(): void
    {
        $this->setName('x-ray')
            ->addArgument('path', InputArgument::IS_ARRAY)
            ->addOption('no-progress', 'P', InputOption::VALUE_NONE, 'Don\'t display the progress bar')
            ->addOption('snippets', 'S', InputOption::VALUE_NONE, 'Display highlighted code snippets')
            ->addOption('summary', 's', InputOption::VALUE_NONE, 'Display a table summarizing the results')
            ->addOption('compact', 'c', InputOption::VALUE_NONE, 'Display results in a compact format')
            ->addOption('ignore', 'i',  InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Ignore one or more files/paths')
            ->setDescription('Scans a directory or filename for calls to ray(), rd() and Ray::*.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->initializeProps($input, $output)
                ->printStatus()
                ->scanPaths()
                ->printResults();
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<fg=yellow;options=bold>Error: </>' . $e->getMessage());

            return Command::FAILURE;
        } catch (MissingArgumentException $e) {
            $this->getApplication()->run(new ArrayInput(['--help']));

            return Command::SUCCESS;
        }

        return count($this->scanResults) ? Command::FAILURE : Command::SUCCESS;
    }

    protected function initializeProps(InputInterface $input, OutputInterface $output): self
    {
        $this->output = $output;
        $this->style = new SymfonyStyle($input, $output);
        $this->config = ConfigurationFactory::create($input)->validate();
        $this->printer = new ConsoleResultsPrinter($output, $this->config);
        $this->scanner = new CodeScanner($this->config, $this->config->paths);

        return $this;
    }

    protected function scanPaths(?array $paths = null): self
    {
        if (! $this->config->hideProgress) {
            $this->style->progressStart(count($this->scanner->paths()));
        }

        $this->scanResults = $this->scanner->scan($paths, function ($path, $results) {
            if (! $this->config->hideProgress) {
                $this->style->progressAdvance();
            }

            if ($this->config->verboseMode) {
                if ($results->hasErrors()) {
                    MessagePrinter::error($this->output, $path . ' <fg=#DC2626>(syntax or parsing error)</>', '   ');

                    return;
                }

                if (count($results->results) > 0) {
                    MessagePrinter::failure($this->output, $path, '   ');
                }

                if (count($results->results) === 0) {
                    MessagePrinter::success($this->output, $path, '   ');
                }
            }
        });

        if (! $this->config->hideProgress) {
            $this->style->progressFinish();
        }

        return $this;
    }

    protected function printResults(): void
    {
        $this->printer->print($this->scanResults);
    }

    protected function printStatus(): self
    {
        MessagePrinter::status($this->output, 'scanning for ray calls...');

        return $this;
    }
}
