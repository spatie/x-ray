<?php

namespace Spatie\RayScan\Tests\TestClasses;

use Spatie\RayScan\Printers\Highlighters\ConsoleColor;

class FakeConsoleColor extends ConsoleColor
{
    public function apply($style, string $text, array $appendStyle = []): string
    {
        return $text;
    }
}
