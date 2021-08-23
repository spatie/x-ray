<?php

namespace Spatie\XRay\Tests\Support;

use PHPUnit\Framework\TestCase;
use Spatie\XRay\Support\Str;

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

    /** @test */
    public function it_gets_the_str_after_the_last_instance_of_a_substr()
    {
        $this->assertEquals('test', Str::afterLast('this is a test', 'a '));
        $this->assertEquals('myMethod()', Str::afterLast('$obj->myMethod()', '->'));
        $this->assertEquals('myMethod()', Str::afterLast('myMethod()', '->'));
        $this->assertEquals('myMethod()', Str::afterLast('myMethod()', ''));
    }
}
