<?php

namespace Permafrost\RayScan\Tests\TestClasses;

use Permafrost\RayScan\Printers\ConsoleResultPrinter;
use Permafrost\RayScan\Results\ScanResult;

class FakeConsoleResultPrinter extends ConsoleResultPrinter
{
    public function print($output, ScanResult $result, bool $colorize = true, bool $printSnippets = true)
    {
        $result->location->filename = str_replace(realpath(__DIR__ . '/../..'), '', $result->location->filename);

        parent::print($output, $result, $colorize, $printSnippets);
    }
}
