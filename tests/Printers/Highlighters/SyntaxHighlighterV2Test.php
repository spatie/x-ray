<?php

namespace Spatie\RayScan\Tests\Printers\Highlighters;

use Permafrost\CodeSnippets\Bounds;
use Permafrost\CodeSnippets\CodeSnippet;
use PHPUnit\Framework\TestCase;
use Spatie\RayScan\Printers\Highlighters\SyntaxHighlighterV2;
use Spatie\RayScan\Tests\TestClasses\FakeConsoleColor;
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

        $this->assertMatchesSnapshot($highlighter->highlightSnippet($snippet, Bounds::create(3, 3)));
    }
}
