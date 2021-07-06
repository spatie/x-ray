<?php

namespace Permafrost\RayScan\Tests\Printers;

use Permafrost\PhpCodeSearch\Code\CodeSnippet;
use Permafrost\PhpCodeSearch\Code\FunctionCallLocation;
use Permafrost\PhpCodeSearch\Results\Nodes\FunctionCallNode;
use Permafrost\PhpCodeSearch\Results\SearchResult;
use Permafrost\PhpCodeSearch\Support\File;
use Permafrost\RayScan\Printers\ConsoleResultPrinter;
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
        $location = new FunctionCallLocation('test', 3, 3);
        $snippet = (new CodeSnippet())
            ->surroundingLine(4)
            ->snippetLineCount(3)
            ->fromFile($file);

        $node = FunctionCallNode::create('test');

        $result = new SearchResult($node, $location, $snippet, basename($file->filename));
        $result->file()->filename = basename($file->filename);
        $output = new FakeOutput();

        $printer = new ConsoleResultPrinter();

        $printer->print($output, $result, true);

        $this->assertMatchesSnapshot($output->writtenData);
    }
}
