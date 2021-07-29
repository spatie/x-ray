<?php

namespace Permafrost\RayScan\Tests\Printers;

use Permafrost\CodeSnippets\CodeSnippet;
use Permafrost\PhpCodeSearch\Code\GenericCodeLocation;
use Permafrost\PhpCodeSearch\Results\Nodes\FunctionCallNode;
use Permafrost\PhpCodeSearch\Results\SearchResult;
use Permafrost\PhpCodeSearch\Support\File;
use Permafrost\RayScan\Printers\ConsoleResultPrinter;
use Permafrost\RayScan\Tests\TestClasses\FakeConsoleColor;
use Permafrost\RayScan\Tests\TestClasses\FakeOutput;
use Permafrost\RayScan\Tests\Traits\CreatesTestConfiguration;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ConsoleResultPrinterTest extends TestCase
{
    use CreatesTestConfiguration;
    use MatchesSnapshots;

    /** @test */
    public function it_prints_results()
    {
        $file = new File(__DIR__.'/../fixtures/fixture1.php');
        $location = new GenericCodeLocation(3, 3);
        $snippet = (new CodeSnippet())
            ->surroundingLine(4)
            ->snippetLineCount(3)
            ->fromFile($file->getRealPath());

        $node = FunctionCallNode::create(new FuncCall(new Name('test'), [], []), []);

        $result = new SearchResult($node, $location, $snippet, basename($file->filename));
        $result->file()->filename = basename($file->filename);
        $output = new FakeOutput();

        $options = ['path' => $file->getRealPath(), '--snippets' => true];
        $printer = new ConsoleResultPrinter($this->createConfiguration(__DIR__, null, $options));

        $printer->consoleColor = new FakeConsoleColor();

        $printer->print($output, $result);

        $this->assertMatchesSnapshot($output->writtenData);
    }
}
