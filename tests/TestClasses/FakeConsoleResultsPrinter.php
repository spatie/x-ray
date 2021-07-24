<?php

namespace Permafrost\RayScan\Tests\TestClasses;

use Permafrost\RayScan\Configuration\Configuration;
use Permafrost\RayScan\Printers\ConsoleResultsPrinter;

class FakeConsoleResultsPrinter extends ConsoleResultsPrinter
{
    public function __construct(Configuration $config)
    {
        $this->config = $config;

        $this->printer = new FakeConsoleResultPrinter($config);
        $this->printer->consoleColor = new FakeConsoleColor();
    }
}
