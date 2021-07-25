<?php

namespace Permafrost\RayScan\Tests\TestClasses;

use Permafrost\RayScan\Printers\Highlighters\ConsoleColor;

class FakeConsoleColor extends ConsoleColor
{
    public function apply($style, string $text, array $appendStyle = []): string
    {
        return $text;
    }

}
