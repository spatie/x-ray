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

class ScanCommand extends Command
{
    protected Configuration $config;

    public ResultsPrinter $printer;

    public OutputInterface $output;

    public CodeScanner $scanner;

    public array $scanResults = [];

    public SymfonyStyle $style;

    protected function configure(): void
    {
        $this->setName($this->getName())
            ->addArgument('path', InputArgument::IS_ARRAY)
            ->addOption('no-progress', 'P', InputOption::VALUE_NONE, 'Don\'t display the progress bar')
            ->addOption('snippets', 'S', InputOption::VALUE_NONE, 'Display highlighted code snippets')
            ->addOption('summary', 's', InputOption::VALUE_NONE, 'Display a table summarizing the results')
            ->addOption('compact', 'c', InputOption::VALUE_NONE, 'Display results in a compact format')
            ->addOption('github', 'g', InputOption::VALUE_NONE, 'Display results in a github annotation format')
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
        } catch (InvalidArgumentException $e) {
            $output->writeln('<fg=yellow;options=bold>Error: </>' . $e->getMessage());

            return Command::FAILURE;
        } catch (MissingArgumentException $e) {
            $this->getApplication()?->run(new ArrayInput(['--help']));

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
