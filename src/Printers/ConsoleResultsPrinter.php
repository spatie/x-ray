<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\PhpCodeSearch\Results\FileSearchResults;
use Symfony\Component\Console\Helper\Table;

class ConsoleResultsPrinter extends ResultsPrinter
{
    public function print(array $results): void
    {
        foreach ($results as $scanResult) {
            foreach ($scanResult->results as $result) {
                $this->printer()->print($this->output, $result);
            }
        }

        $this->printSummary($results);
    }

    public function printSummary(array $results): void
    {
        [$files, $functions] = $this->summarizeCalls($results);

        $totalCalls = array_sum(array_values($functions));
        $totalFiles = count($files);

        if ($this->config->showSummary) {
            $this->renderSummaryTable($files);
        }

        if ($this->config->showSnippets) {
            $this->output->writeln('');
            $this->output->writeln(' ---');
        }

        if ($totalFiles === 0) {
            $this->output->writeln(" No function or static method calls found.");
        }

        if ($totalFiles > 0) {
            $this->output->writeln(" Found {$totalCalls} function calls in {$totalFiles} files.");
        }
    }

    protected function summarizeCalls(array $results): array
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

        return [$files, $functions];
    }

    protected function printer(): ResultPrinter
    {
        return $this->printer ?? new ConsoleResultPrinter($this->config);
    }

    protected function renderSummaryTable(array $fileCounts): void
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
