<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\PhpCodeSearch\Results\FileSearchResults;
use Permafrost\PhpCodeSearch\Results\SearchResult;
use Permafrost\RayScan\Configuration\Configuration;
use Permafrost\RayScan\Results\ScanResults;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleResultsPrinter extends ResultsPrinter
{
    /** @var ConsoleResultPrinter|null */
    public $printer = null;

    public function __construct()
    {
        $this->printer = new ConsoleResultPrinter();
    }

    public function print(OutputInterface $output, array $results, Configuration $config): void
    {
        $printer = $this->printer ?? new ConsoleResultPrinter();

        foreach ($results as $scanResult) {
            foreach($scanResult->results as $result) {
                $printer->print($output, $result, true, ! $config->hideSnippets);
            }
        }

        $this->printSummary($output, $results, $config);
    }

    public function printSummary(OutputInterface $output, array $results, Configuration $config): void
    {
        $files = [];
        $functions = [];

        // count number of files and functions found
        /** @var FileSearchResults $scanResult */
        foreach($results as $scanResult) {
            foreach ($scanResult->results as $result) {
                if (!isset($files[$result->file()->filename])) {
                    $files[$result->file()->filename] = 0;
                }
                $files[$result->file()->filename]++;

                if (!isset($functions[$result->location->name])) {
                    $functions[$result->location->name] = 0;
                }
                $functions[$result->location->name]++;
            }
        }

        $totalCalls = array_sum(array_values($functions));
        $totalFiles = count($files);

        $output->writeln('');
        $output->writeln('---');

        if ($totalFiles === 0) {
            $output->writeln("No function or static method calls found.");
        }

        if ($totalFiles > 0) {
            $output->writeln("Found {$totalCalls} function calls in {$totalFiles} files.");
        }
    }

}
