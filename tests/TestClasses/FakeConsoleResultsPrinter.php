<?php

namespace Permafrost\RayScan\Tests\TestClasses;

use Permafrost\RayScan\Printers\ConsoleResultsPrinter;

class FakeConsoleResultsPrinter extends ConsoleResultsPrinter
{
    public function __construct()
    {
        $this->printer = new FakeConsoleResultPrinter();
    }
}
