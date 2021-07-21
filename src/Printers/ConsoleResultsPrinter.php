<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\PhpCodeSearch\Results\FileSearchResults;
use Symfony\Component\Console\Helper\Table;

class ConsoleResultsPrinter extends ResultsPrinter
{
    public function print(array $results): void
    {
        foreach ($results as $scanResult) {
            foreach($scanResult->results as $result) {
                $this->printer()->print($this->output, $result, ! $this->config->hideSnippets);
            }
        }

        $this->printSummary($results);
    }

    public function printSummary(array $results): void
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

                if (!isset($functions[$result->node->name()])) {
                    $functions[$result->node->name()] = 0;
                }

                $functions[$result->node->name()]++;
            }
        }

        $totalCalls = array_sum(array_values($functions));
        $totalFiles = count($files);

        $this->output->writeln('');
        $this->output->writeln('---');

        if ($totalFiles === 0) {
            $this->output->writeln("No function or static method calls found.");
        }

        if ($totalFiles > 0) {
            $this->renderSummaryTable($files);

            $this->output->writeln("Found {$totalCalls} function calls in {$totalFiles} files.");
        }
    }

    protected function printer(): ResultPrinter
    {
        return $this->printer ?? new ConsoleResultPrinter();
    }

    protected function renderSummaryTable(array $fileCounts)
    {
        $rows = [];

        foreach($fileCounts as $filename => $count) {
            $rows[] = [$this->makeFilenameRelative($filename), $count];
        }

        $table = new Table($this->output);

        $table
            ->setHeaders(['Filename', 'Call Count'])
            ->setRows($rows);

        $table->render();
    }

    protected function makeFilenameRelative(string $filename): string
    {
        return str_replace(getcwd() . DIRECTORY_SEPARATOR, './', $filename);
    }
}
