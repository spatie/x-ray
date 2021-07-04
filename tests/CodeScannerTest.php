<?php

namespace Permafrost\RayScan\Tests;

use Permafrost\RayScan\CodeScanner;
use Permafrost\RayScan\Configuration\Configuration;
use Permafrost\RayScan\Support\File;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

class CodeScannerTest extends TestCase
{
    protected function getConfig(): Configuration
    {
        return new Configuration(__DIR__, false, false);
    }

    /** @test */
    public function it_finds_function_calls()
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $scanner = new CodeScanner($this->getConfig(), $parser);
        $file = new File(__DIR__ . '/fixtures/fixture1.php');

        $ast = $parser->parse($file->contents());
        $calls = $scanner->findFunctionCalls($ast, 'ray', 'strtolower');

        $this->assertCount(2, $calls);
    }

    /** @test */
    public function it_scans_a_file()
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $scanner = new CodeScanner($this->getConfig(), $parser);
        $file = new File(__DIR__ . '/fixtures/fixture1.php');

        $results = $scanner->scan($file);

        $this->assertFalse($results->hasErrors());
        $this->assertCount(1, $results->results);

        $this->assertEquals('ray', $results->results[0]->location->name);
        $this->assertEquals(2, $results->results[0]->location->startLine);
    }

    /** @test */
    public function it_returns_an_error_for_parsing_errors()
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $scanner = new CodeScanner($this->getConfig(), $parser);
        $file = new File(__DIR__ . '/fixtures/fixture2.php');

        $results = $scanner->scan($file);

        $this->assertTrue($results->hasErrors());
        $this->assertCount(1, $results->errors);
    }

}
