<?php

namespace Spatie\RayScan\Tests\TestClasses;

use Spatie\RayScan\Configuration\Configuration;
use Spatie\RayScan\Printers\ConsoleResultsPrinter;

class FakeConsoleResultsPrinter extends ConsoleResultsPrinter
{
    public function __construct(Configuration $config)
    {
        $this->config = $config;

        $this->printer = new FakeConsoleResultPrinter($config);
        $this->printer->consoleColor = new FakeConsoleColor();
    }
}
