<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\CodeSnippets\Bounds;
use Permafrost\PhpCodeSearch\Results\SearchResult;
use Permafrost\RayScan\Printers\Highlighters\ConsoleColor;
use Permafrost\RayScan\Printers\Highlighters\SyntaxHighlighterV2;
use Permafrost\RayScan\Support\Str;
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
        $this->printHeader($output, $result);

        if ($this->config->showSnippets) {
            $bounds = Bounds::create($result->location->startLine(), $result->location->endLine());
            $line = (new SyntaxHighlighterV2($this->consoleColor))
                ->highlightSnippet($result->snippet, $bounds);

            $output->writeln($line);
        }
    }

    protected function printHeader(OutputInterface $output, SearchResult $result): void
    {
        $filename = str_replace(getcwd() . DIRECTORY_SEPARATOR, './', $result->file()->filename);

        if ($this->config->showSnippets) {
            $output->writeln('');
        }

        $nodeName = Str::afterLast($result->node->name(), '->');

        $output->writeln(" Filename: {$filename}");
        $output->writeln(" Line Num: <options=bold>{$result->location->startLine}</>");
        $output->writeln(" Found   : <fg=#e53e3e>{$nodeName}</>");
        $output->writeln(' ------');
    }
}
