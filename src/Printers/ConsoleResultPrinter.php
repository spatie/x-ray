<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\PhpCodeSearch\Results\SearchResult;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleResultPrinter extends ResultPrinter
{
    /**
     * @param OutputInterface $output
     */
    public function print($output, SearchResult $result, bool $colorize = true, bool $printSnippets = true)
    {
        $this->initializeFormatter($output);

        $this->printHeader($output, $result);

        if ($printSnippets) {
            $highlighter = new SyntaxHighlighter();

            foreach ($result->snippet->getCode() as $lineNum => $line) {
                $line = $highlighter->highlightLine($line, $result->node->name(), $lineNum, $result->location->startLine());

                if (! $colorize) {
                    $line = preg_replace('~\<.g=[^>]+\>~', '', $line);
                    $line = str_replace('</>', '', $line);
                }

                // echo "$line\n";
                $output->writeln($line);
            }
        }
    }

    protected function initializeFormatter($output): void
    {
        $map = [
            'comment' => ['#334155', null],
            'keyword' => ['#dd6b20', null],
            'line-num' => ['#4a5568', null],
            'method' => ['#ecc94b', null],
            'str' => ['#38a169', null],
            'target-line' => [null, '#2d3748'],
            'line-num-target' => ['#ed64a6', '#2d3748'],
            'variable' => ['#9f7aea', null],
            'pointer' => ['#ed64a6', null],
            'target-call' => ['#e53e3e', null],
        ];

        foreach($map as $name => $colors) {
            [$fg, $bg] = $colors;

            $outputStyle = new OutputFormatterStyle($fg, $bg);
            $output->getFormatter()->setStyle($name, $outputStyle);

            // create a "NNN-target" tag as well for highlighting the target line bg
            if ($bg === null && ! isset($map["{$name}-target"])) {
                $outputStyle = new OutputFormatterStyle($fg, $map['target-line'][1]);
                $output->getFormatter()->setStyle("{$name}-target", $outputStyle);
            }
        }
    }

    protected function printHeader(OutputInterface $output, SearchResult $result): void
    {
        $filename = str_replace(getcwd() . DIRECTORY_SEPARATOR, './', $result->file()->filename);

        $output->writeln('');
        $output->writeln(" Filename: {$filename}");
        $output->writeln(" Line Num: {$result->location->startLine}");
        $output->writeln(" Found   : <target-call>{$result->node->name()}</target-call>");
        $output->writeln(" ------");
    }
}
