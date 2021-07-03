<?php

namespace Permafrost\RayScan\Tests;

use Permafrost\RayScan\Code\CodeSnippet;
use Permafrost\RayScan\Support\File;
use PHPUnit\Framework\TestCase;

class CodeSnippetTest extends TestCase
{
    /** @test */
    public function it_sets_the_code_property_to_an_empty_array_if_the_file_does_not_exist()
    {
        $file = new File(__DIR__ . '/fixtures/missing.php');
        $snippet = (new CodeSnippet())->fromFile($file);

        $this->assertCount(0, $snippet->getCode());
    }

}
