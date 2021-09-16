<?php

namespace Spatie\XRay\Printers;

use Permafrost\PhpCodeSearch\Results\FileSearchResults;
use Spatie\XRay\Printers\Highlighters\ConsoleColor;
use Symfony\Component\Console\Helper\Table;

class ConsoleResultsPrinter extends ResultsPrinter
{
    public ?ConsoleColor $consoleColor = null;

    public function print(array $results): void
    {
        $this->printer()->consoleColor = $this->consoleColor;

        MessagePrinter::status($this->output, 'scan complete.');

        if (count($results)) {
            $this->output->writeln('');
        }

        if (! $this->config->showSummary) {
            foreach ($results as $scanResult) {
                foreach ($scanResult->results as $result) {
                    $this->printer()->print($this->output, $result);
                }
            }
        }

        $this->printSummary($results);
    }

    public function printSummary(array $results): void
    {
        [$files, $functions] = $this->summarizeCalls($results);

        $totalCalls = array_sum(array_values($functions));
        $totalFiles = count($files);

        if ($totalFiles === 0) {
            MessagePrinter::success($this->output, 'No references to ray were found.');

            return;
        }

        if ($this->config->showSummary) {
            $this->renderSummaryTable($files);
        }

        if (! $this->config->isDefaultMode()) {
            $this->output->writeln('');
        }

        $callsWord = $totalCalls === 1 ? 'call' : 'calls';
        $filesWord = $totalFiles === 1 ? 'file' : 'files';

        MessagePrinter::warning($this->output, "Found {$totalCalls} {$callsWord} in {$totalFiles} {$filesWord}.");
    }

    protected function summarizeCalls(array $results): array
    {
        $files = [];
        $functions = [];

        // count number of files and functions found
        /** @var FileSearchResults $scanResult */
        foreach ($results as $scanResult) {
            foreach ($scanResult->results as $result) {
                if (! isset($files[$result->file()->filename])) {
                    $files[$result->file()->filename] = 0;
                }

                $files[$result->file()->filename]++;

                if (! isset($functions[$result->node->name()])) {
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

        foreach ($fileCounts as $filename => $count) {
            $rows[] = [$this->makeFilenameRelative($filename), $count];
        }

        $table = new Table($this->output);

        $table
            ->setHeaders(['Filename ', 'Call Count '])
            ->setRows($rows);

        $table->render();
    }

    protected function makeFilenameRelative(string $filename): string
    {
        return str_replace(getcwd() . DIRECTORY_SEPARATOR, './', $filename);
    }
}
