<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\RayScan\Results\ScanResult;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleResultPrinter extends ResultPrinter
{


    /**
     * @param OutputInterface $output
     */
    public function print($output, ScanResult $result, bool $colorize = true)
    {
        $this->initializeFormatter($output);

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
            'ray-call' => ['#e53e3e', null],
        ];
        //fg=#ed64a6;bg=#2d3748

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

    protected function printHeader(OutputInterface $output, ScanResult $result): void
    {
        $output->writeln('');
        $output->writeln(" Filename: <href=file://{$result->location->filename}>{$result->location->filename}</>");
        $output->writeln(" Line Num: {$result->location->startLine}");
        $output->writeln(" Function: <ray-call>{$result->location->name}()</ray-call>");
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

        // use the *-target tag variant to allow bg highlighting of the target line
        $line = preg_replace('~<([\w-]+)>(.+)</(\1)>~', '<$1-target>$2</$1-target>', $line);

        return "<target-line>{$line}</target-line>";
    }


    protected function createOutputLine(string $line, int $currentLineNum, ScanResult $result): string
    {
        $isTargetLine = $currentLineNum === $result->location->startLine;
        $prefix = $isTargetLine ? ' <pointer>══════❱</pointer>' : '        ';

        return sprintf(" [<line-num>% 4d</line-num>]%-4s%-60s", $currentLineNum, $prefix, $line);
    }

    protected function highlightTargetFunction(string $line, int $currentLineNum, ScanResult $result): string
    {
        $isTargetLine = $currentLineNum === $result->location->startLine;

        if ($isTargetLine) {
            //$line = str_replace("{$result->location->name}(", "<ray-call>{$result->location->name}</ray-call>(", $line);
            $line = preg_replace('~' . $result->location->name . '\s*\(~', "<ray-call>{$result->location->name}</ray-call>(", $line);
        }

        return $line;
    }

    protected function highlightSyntax(string $line): string
    {
        // comments
        $line = preg_replace('~(//.*)$~', '<comment>$1</comment>', $line);
        $line = preg_replace('~(/\*\*?.*\*/\s*)$~', '<comment>$1</comment>', $line);

        // variables
        $line = preg_replace('~(\$\w+)~', '<variable>$1</variable>', $line);

        // method calls
        $line = preg_replace('~(->)(\w+)\s*\(~', '$1<method>$2</method>(', $line);

        // strings
        $line = preg_replace("~('[^']+')~", '<str>$1</str>', $line);
        $line = preg_replace('~("[^"]+")~', '<str>$1</str>', $line);

        return $line;
    }

    protected function highlightReservedKeywords(string $line)
    {
        $keywords = 'abstract as bool catch class echo extends final for foreach function if implements instanceof int interface ' .
            'namespace new null PHP_EOL private protected public return self static static string try use void';

        foreach(explode(' ', $keywords) as $keyword) {
            $line = preg_replace('~\b('.$keyword.')\b~', '<keyword>$1</keyword>', $line);
        }

        return $line;
    }
}
