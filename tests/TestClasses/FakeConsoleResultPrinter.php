<?php

namespace Spatie\RayScan\Tests\TestClasses;

use Permafrost\PhpCodeSearch\Results\SearchResult;
use Spatie\RayScan\Printers\ConsoleResultPrinter;

class FakeConsoleResultPrinter extends ConsoleResultPrinter
{
    public function print($output, SearchResult $result): void
    {
        $result->file()->filename = str_replace(realpath(__DIR__ . '/../..'), '', $result->file()->getRealPath());

        parent::print($output, $result);
    }
}
