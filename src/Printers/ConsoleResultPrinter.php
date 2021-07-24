<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\PhpCodeSearch\Results\SearchResult;
use Permafrost\RayScan\Printers\Highlighters\ConsoleColor;
use Permafrost\RayScan\Printers\Highlighters\SyntaxHighlighter;
use Permafrost\RayScan\Printers\Highlighters\SyntaxHighlighterV2;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleResultPrinter extends ResultPrinter
{
    /** @var ConsoleColor|null */
    public $consoleColor = null;

    /**
     * @param OutputInterface $output
     */
    public function print($output, SearchResult $result): void
    {
        $this->initializeFormatter($output);
        $this->printHeader($output, $result);

        if ($this->config->showSnippets) {
//            $highlighter = new SyntaxHighlighter();

            $testHighlighter = new SyntaxHighlighterV2($this->consoleColor);

            $output->writeln($testHighlighter->highlightSnippet($result->snippet));

//            foreach ($result->snippet->getCode() as $lineNum => $line) {
//                $name = $result->node->name();
//                $startLine = $result->location->endLine();
//
//                $line = $highlighter->highlightLine($line, $name, $lineNum, $startLine);
//
//                $output->writeln($line);
//            }
        }
    }

    protected function initializeFormatter($output): void
    {
        $colorMap = [
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

        foreach($colorMap as $name => $colors) {
            [$fg, $bg] = $colors;

            $outputStyle = new OutputFormatterStyle($fg, $bg);
            $output->getFormatter()->setStyle($name, $outputStyle);

            // create a "NNN-target" tag as well for highlighting the target line bg
            if ($bg === null && ! isset($colorMap["{$name}-target"])) {
                $outputStyle = new OutputFormatterStyle($fg, $colorMap['target-line'][1]);
                $output->getFormatter()->setStyle("{$name}-target", $outputStyle);
            }
        }
    }

    protected function printHeader(OutputInterface $output, SearchResult $result): void
    {
        $filename = str_replace(getcwd() . DIRECTORY_SEPARATOR, './', $result->file()->filename);

        if ($this->config->showSnippets) {
            $output->writeln('');
        }

        $output->writeln(" Filename: {$filename}");
        $output->writeln(" Line Num: {$result->location->startLine}");
        $output->writeln(" Found   : <target-call>{$result->node->name()}</target-call>");
        $output->writeln(' ------');
    }
}
