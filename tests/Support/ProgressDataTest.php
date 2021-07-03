<?php

namespace Permafrost\RayScan\Tests\Support;

use Permafrost\RayScan\Support\ProgressData;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ProgressDataTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_creates_a_progress_data_object_from_an_array()
    {
        $data = [
            'total' => 321,
            'current' => 123,
            'position' => 12,
            'scale' => 321,
        ];

        $dto = ProgressData::create($data);

        $this->assertMatchesObjectSnapshot($dto);
    }

}
