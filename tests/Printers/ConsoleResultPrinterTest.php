<?php

namespace Permafrost\RayScan\Tests\Printers;

use Permafrost\RayScan\Code\CodeSnippet;
use Permafrost\RayScan\Code\FunctionCallLocation;
use Permafrost\RayScan\Printers\ConsoleResultPrinter;
use Permafrost\RayScan\Results\ScanResult;
use Permafrost\RayScan\Support\File;
use Permafrost\RayScan\Tests\TestClasses\FakeOutput;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ConsoleResultPrinterTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_prints_results()
    {
        $file = new File(__DIR__.'/../fixtures/fixture1.php');
        $location = new FunctionCallLocation('test', $file->getRealPath(), 3, 3);
        $snippet = (new CodeSnippet())
            ->surroundingLine(4)
            ->snippetLineCount(3)
            ->fromFile($file);

        $location->filename = basename($location->filename);
        $result = new ScanResult($location, $snippet);
        $output = new FakeOutput();

        $printer = new ConsoleResultPrinter();

        $printer->print($output, $result, false);

        $this->assertMatchesSnapshot($output->writtenData);
    }
}
