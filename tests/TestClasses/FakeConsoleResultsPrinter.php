<?php

namespace Spatie\XRay\Tests\TestClasses;

use Spatie\XRay\Configuration\Configuration;
use Spatie\XRay\Printers\ConsoleResultsPrinter;

class FakeConsoleResultsPrinter extends ConsoleResultsPrinter
{
    public function __construct(Configuration $config)
    {
        $this->config = $config;

        $this->printer = new FakeConsoleResultPrinter($config);
        $this->printer->consoleColor = new FakeConsoleColor();
    }
}
