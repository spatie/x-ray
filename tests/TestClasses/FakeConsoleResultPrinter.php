<?php

namespace Spatie\XRay\Tests\TestClasses;

use Permafrost\PhpCodeSearch\Results\SearchResult;
use Spatie\XRay\Printers\ConsoleResultPrinter;

class FakeConsoleResultPrinter extends ConsoleResultPrinter
{
    public function print($output, SearchResult $result): void
    {
        $result->file()->filename = str_replace(realpath(__DIR__ . '/../..'), '', $result->file()->getRealPath());

        parent::print($output, $result);
    }
}
