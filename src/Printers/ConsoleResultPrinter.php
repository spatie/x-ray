<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\CodeSnippets\Bounds;
use Permafrost\PhpCodeSearch\Results\SearchResult;
use Permafrost\RayScan\Printers\Highlighters\ConsoleColor;
use Permafrost\RayScan\Printers\Highlighters\SyntaxHighlighterV2;
use Permafrost\RayScan\Support\Str;
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
        $this->printResultLine($output, $result);

        if ($this->config->showSnippets) {
            $bounds = Bounds::create($result->location->startLine(), $result->location->endLine());
            $line = (new SyntaxHighlighterV2($this->consoleColor))
                ->highlightSnippet($result->snippet, $bounds);

            $output->writeln($line);
        }
    }

    protected function printResultLine(OutputInterface $output, SearchResult $result)
    {
        $filename = str_replace(getcwd() . DIRECTORY_SEPARATOR, './', $result->file()->filename);

        if ($this->config->compactMode) {
            return $this->printCompactResultLine($output, $result);
        }

        $nodeName = Str::afterLast($result->node->name(), '->');

        $output->writeln(" <fg=#78716C;options=bold>❱</> <fg=#e53e3e>{$nodeName}</>");
        $output->writeln(" <fg=#78716C;options=bold>❱</> {$filename}:<options=bold>{$result->location->startLine}</>");

        //e53e3e
       $output->writeln('');
    }

    protected function printCompactResultLine(OutputInterface $output, SearchResult $result)
    {
        $filename = str_replace(getcwd() . DIRECTORY_SEPARATOR, './', $result->file()->filename);

        $output->writeln(" {$filename}:<fg=#52525B>{$result->location->startLine}</>");

        if ($this->config->showSnippets) {
            $output->writeln('');
        }

        return true;
    }
}
