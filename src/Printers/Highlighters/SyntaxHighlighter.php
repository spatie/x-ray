<?php

namespace Permafrost\RayScan\Printers\Highlighters;

class SyntaxHighlighter extends BasicSnippetHighlighter
{
    public function highlightLine(string $line, string $targetName, int $currentLineNum, int $targetLineNumber): string
    {
        //print_r($this->tokenize($line));

        $line = $this->standardizeLineLength($line);
        $line = $this->highlightTargetFunction($line, $targetName, $currentLineNum, $targetLineNumber);
        $line = $this->highlightSyntax($line);
        $line = $this->highlightReservedKeywords($line);
        $line = $this->createTargetLinePointer($line, $currentLineNum, $targetLineNumber);
        $line = $this->highlightTargetLineBackground($line, $currentLineNum, $targetLineNumber);

        return $line;
    }



    protected function standardizeLineLength(string $line, int $length = 80): string
    {
        return str_pad($line, $length, ' ');
    }

    protected function highlightTargetLineBackground(string $line, int $currentLineNum, int $startLine): string
    {
        $isTargetLine = $currentLineNum === $startLine;

        if (! $isTargetLine) {
            return $line;
        }

        // use the *-target tag variant to allow bg highlighting of the target line
        $line = preg_replace('~<([\w-]+)-target>~', '<$1>', $line);
        $line = preg_replace('~</([\w-]+)-target>~', '</$1>', $line);

        $line = preg_replace('~<([\w-]+)>~', '<$1-target>', $line);
        $line = preg_replace('~</([\w-]+)>~', '</$1-target>', $line);

        return "<target-line>{$line}</target-line>";
    }

    protected function highlightTargetFunction(string $line, string $name, int $currentLineNum, int $startLine): string
    {
        $isTargetLine = $currentLineNum === $startLine;

        if ($isTargetLine) {
            // match strings like 'Ray::' and 'ray(' and '\ray('
            $line = preg_replace('~(\\\\?' . $name . ')(::|\s*\()~', '<target-call>$1</target-call>$2', $line);
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
        $line = preg_replace('~(::|->)(\w+)\s*\(~', '$1<method>$2</method>(', $line);

        // strings
        $line = preg_replace("~('[^']+')~", '<str>$1</str>', $line);
        $line = preg_replace('~("[^"]+")~', '<str>$1</str>', $line);

        return $line;
    }

    protected function highlightReservedKeywords(string $line): string
    {
        $keywords = '<' .'?php abstract as bool catch class echo extends false final for foreach function if implements instanceof int interface ' .
            'namespace new null private protected public return self static string true try use void';

        // highlight PHP_* constants
        $line = preg_replace('~\b(PHP_[A-Z_]+)\b~', '<keyword>$1</keyword>', $line);

        foreach(explode(' ', $keywords) as $keyword) {
            $line = preg_replace('~('.preg_quote($keyword, '~').')\b~', '<keyword>$1</keyword>', $line);
        }

        return $line;
    }
}
