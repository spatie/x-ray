<?php

namespace Permafrost\RayScan\Tests\Support;

use Permafrost\RayScan\Support\Progress;
use Permafrost\RayScan\Support\ProgressData;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ProgressTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_initializes_correctly()
    {
        $progress = new Progress(543);

        $this->assertMatchesObjectSnapshot($progress);
    }

    /** @test */
    public function it_advances_the_current_value()
    {
        $progress = new Progress(543);

        $this->assertEquals(0, $progress->current);

        $progress->advance(1);
        $this->assertEquals(1, $progress->current);

        $progress->advance(3);
        $this->assertEquals(4, $progress->current);
    }

    /** @test */
    public function it_advances_the_current_value_and_calls_the_callback()
    {
        $progress = new Progress(543);
        $data = (object)['called' => false];

        $progress->withCallback(function($param) use ($data) {
            $data->called = true;
        });

        $this->assertFalse($data->called);

        $progress->advance(1);
        $this->assertTrue($data->called);
    }
}
