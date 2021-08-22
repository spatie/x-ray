<?php

namespace Spatie\XRay\Tests\TestClasses;

use Spatie\XRay\Printers\Highlighters\ConsoleColor;

class FakeConsoleColor extends ConsoleColor
{
    public function apply($style, string $text, array $appendStyle = []): string
    {
        return $text;
    }
}
