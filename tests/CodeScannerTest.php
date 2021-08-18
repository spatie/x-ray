<?php

namespace Permafrost\RayScan\Tests;

use Permafrost\PhpCodeSearch\Support\File;
use Permafrost\RayScan\CodeScanner;
use Permafrost\RayScan\Configuration\Configuration;
use PHPUnit\Framework\TestCase;

class CodeScannerTest extends TestCase
{
    protected function getConfig(): Configuration
    {
        return new Configuration([__DIR__], false, false, false);
    }

    /** @test */
    public function it_scans_a_file()
    {
        $file = new File(__DIR__ . '/fixtures/fixture1.php');
        $scanner = new CodeScanner($this->getConfig(), $file->getRealPath());


        $results = $scanner->scanFile($file->getRealPath());

        $this->assertFalse($results->hasErrors());
        $this->assertCount(1, $results->results);

        $this->assertEquals('ray', $results->results[0]->node->name());
        $this->assertEquals(2, $results->results[0]->location->startLine);
    }

    /** @test */
    public function it_returns_an_error_for_parsing_errors()
    {
        $file = new File(__DIR__ . '/fixtures/fixture2.php');
        $scanner = new CodeScanner($this->getConfig(), $file->getRealPath());

        $results = $scanner->scanFile($file->getRealPath());

        $this->assertTrue($results->hasErrors());
        $this->assertCount(1, $results->errors);
    }
}
