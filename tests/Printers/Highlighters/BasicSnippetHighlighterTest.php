<?php

namespace Permafrost\RayScan\Tests\Printers\Highlighters;

use Permafrost\RayScan\Printers\Highlighters\BasicSnippetHighlighter;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class BasicSnippetHighlighterTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_highlights_lines_when_not_on_the_target_line()
    {
        $highlighter = new BasicSnippetHighlighter();

        $line = $highlighter->highlightLine('$test = 123;', 'test', 1, 2);

        $this->assertMatchesSnapshot($line);
    }

    /** @test */
    public function it_highlights_lines_when_on_the_target_line()
    {
        $highlighter = new BasicSnippetHighlighter();

        $line = $highlighter->highlightLine('$test = 123;', 'test', 1, 1);

        $this->assertMatchesSnapshot($line);
    }

}
