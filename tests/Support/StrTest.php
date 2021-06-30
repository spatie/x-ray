<?php

namespace Permafrost\RayScan\Tests\Support;

use Permafrost\RayScan\Support\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    /** @test */
    public function it_checks_if_a_string_starts_with_another_string()
    {
        $this->assertTrue(Str::startsWith('ABC', 'A'));
        $this->assertTrue(Str::startsWith('ABC', 'AB'));
        $this->assertFalse(Str::startsWith('ABC', 'B'));
        $this->assertFalse(Str::startsWith('ABC', 'a'));
    }
}
