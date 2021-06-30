<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\RayScan\Results\ScanResult;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleResultPrinter extends ResultPrinter
{
    public function print(OutputInterface $output, ScanResult $result, bool $colorize = true)
    {
        $this->printHeader($output, $result);

        foreach ($result->snippet->getCode() as $lineNum => $line) {
            $line = $this->standardizeLineLength($line);
            $line = $this->highlightTargetFunction($line, $lineNum, $result);
            $line = $this->highlightSyntax($line);
            $line = $this->highlightReservedKeywords($line);
            $line = $this->createOutputLine($line, $lineNum, $result);
            $line = $this->highlightTargetLineBackground($line, $lineNum, $result);

            if (! $colorize) {
                $line = preg_replace('~\<.g=[^>]+\>~', '', $line);
                $line = str_replace('</>', '', $line);
            }

            $output->writeln($line);
        }

        $output->writeln('');
    }

    protected function printHeader(OutputInterface $output, ScanResult $result): void
    {
        $output->writeln('');
        $output->writeln(" Filename: {$result->location->filename}");
        $output->writeln(" Line Num: {$result->location->startLine}");
        $output->writeln(" Function: {$result->location->name}()");
        $output->writeln(" ------");
    }

    protected function standardizeLineLength(string $line, int $length = 80): string
    {
        return str_pad($line, $length, ' ');
    }

    protected function highlightTargetLineBackground(string $line, int $currentLineNum, ScanResult $result): string
    {
        $isTargetLine = $currentLineNum === $result->location->startLine;

        if (! $isTargetLine) {
            return $line;
        }


        return "<bg=#2d3748>{$line}</>";
    }


    protected function createOutputLine(string $line, int $currentLineNum, ScanResult $result): string
    {
        $isTargetLine = $currentLineNum === $result->location->startLine;
        $lineNumColor = $isTargetLine ? '#ed64a6' : '#4a5568';
        $prefix = $isTargetLine ? ' <fg=#ed64a6;bg=#2d3748>══════❱</>' : '        ';

        $line = sprintf(" [<fg=$lineNumColor>% 4d</>]%-4s%-60s", $currentLineNum, $prefix, $line);

        if ($isTargetLine) {
            $line = str_replace('<fg=', '<bg=#2d3748;fg=', $line);
        }

        return $line;
    }

    protected function highlightTargetFunction(string $line, int $currentLineNum, ScanResult $result): string
    {
        $isTargetLine = $currentLineNum === $result->location->startLine;

        if ($isTargetLine) {
            $line = str_replace($result->location->name . '(', '<fg=#e53e3e>' . $result->location->name . '</>(', $line);
        }

        return $line;
    }

    protected function highlightSyntax(string $line): string
    {
        // comments
        $line = preg_replace('~(//.*)$~', '<fg=#334155>$1</>', $line);
        $line = preg_replace('~(/\*\*?.*\*/\s*)$~', '<fg=#334155>$1</>', $line);

        // variables
        $line = preg_replace('~(\$\w+)~', '<fg=#9f7aea>$1</>', $line);

        // method calls
        $line = preg_replace('~(->)(\w+)\s*\(~', '$1<fg=#ecc94b>$2</>(', $line);

        // strings
        $line = preg_replace("~('[^']+')~", '<fg=#38a169>$1</>', $line);
        $line = preg_replace('~("[^"]+")~', '<fg=#38a169>$1</>', $line);

        return $line;
    }

    protected function highlightReservedKeywords(string $line)
    {
        $keywords = 'abstract as bool catch class echo extends final for foreach function if implements instanceof int interface ' .
            'namespace new null PHP_EOL private protected public return self static static string try use void';

        foreach(explode(' ', $keywords) as $keyword) {
            $line = preg_replace('~\b('.$keyword.')\b~', '<fg=#dd6b20>$1</>', $line);
        }

        return $line;
    }
}
