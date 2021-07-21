<?php

namespace Permafrost\RayScan\Printers\Highlighters;

class BasicSnippetHighlighter implements Highlighter
{
    public function highlightLine(string $line, string $targetName, int $currentLineNum, int $targetLineNumber): string
    {
        $line = $this->createTargetLinePointer($line, $currentLineNum, $targetLineNumber);

        return $line;
    }

    protected function createTargetLinePointer(string $line, int $currentLineNum, int $startLine): string
    {
        $isTargetLine = $currentLineNum === $startLine;
        $prefix = $isTargetLine ? ' <pointer>══════❱</pointer>' : '        ';

        return sprintf(" [<line-num>% 4d</line-num>]%-4s%-60s", $currentLineNum, $prefix, $line);
    }
}
