<?php

namespace Permafrost\RayScan\Printers\Highlighters;

interface Highlighter
{
    public function highlightLine(string $line, string $targetName, int $currentLineNum, int $targetLineNumber): string;
}
