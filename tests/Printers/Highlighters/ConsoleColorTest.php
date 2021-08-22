<?php

namespace Spatie\RayScan\Tests\Printers\Highlighters;

use PHPUnit\Framework\TestCase;
use Spatie\RayScan\Printers\Highlighters\ConsoleColor;
use Spatie\Snapshots\MatchesSnapshots;

class ConsoleColorTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_gets_the_defined_themes()
    {
        $color = new ConsoleColor();
        $color->setThemes([]);

        $this->assertEquals([], $color->getThemes());
    }

    /** @test */
    public function it_sets_the_defined_themes()
    {
        $themes = ['token_string' => ['color_70']];
        $color = new ConsoleColor();
        $color->setThemes($themes);

        $this->assertEquals($themes, $color->getThemes());
    }

    /** @test */
    public function it_throws_an_error_when_adding_an_invalid_theme()
    {
        $this->expectException(\InvalidArgumentException::class);
        $color = new ConsoleColor();
        $color->addTheme('test', 123);
    }

    /** @test */
    public function it_removes_a_theme()
    {
        $color = new ConsoleColor();
        $color->addTheme('test', ['color_70']);

        $this->assertEquals(['test' => ['color_70']], $color->getThemes());

        $color->removeTheme('test');

        $this->assertEquals([], $color->getThemes());
    }

    /** @test */
    public function it_gets_possible_styles()
    {
        $color = new ConsoleColor();

        $this->assertMatchesSnapshot($color->getPossibleStyles());
    }

    /** @test */
    public function it_throws_an_exception_when_adding_a_theme_with_an_invalid_style()
    {
        $this->expectException(\Exception::class);
        $color = new ConsoleColor();
        $color->addTheme('test', ['color_AAAAA']);
    }

    /** @test */
    public function it_sets_and_gets_the_forced_style_property()
    {
        $color = new ConsoleColor();

        $color->setForceStyle(true);
        $this->assertTrue($color->isStyleForced());

        $color->setForceStyle(false);
        $this->assertFalse($color->isStyleForced());
    }
}
