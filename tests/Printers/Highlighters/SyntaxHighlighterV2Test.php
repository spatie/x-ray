<?php

namespace Permafrost\RayScan\Tests\Printers\Highlighters;

use Permafrost\CodeSnippets\CodeSnippet;
use Permafrost\RayScan\Printers\Highlighters\SyntaxHighlighterV2;
use Permafrost\RayScan\Tests\TestClasses\FakeConsoleColor;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class SyntaxHighlighterV2Test extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_highlights_snippets()
    {
        $highlighter = new SyntaxHighlighterV2(new FakeConsoleColor());

        $snippet = (new CodeSnippet())
            ->surroundingLine(3)
            ->snippetLineCount(5)
            ->fromFile(__DIR__ . '/../../fixtures/fixture1.php');

        $this->assertMatchesSnapshot($highlighter->highlightSnippet($snippet, 3, 3));
    }

}
