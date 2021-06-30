<?php

namespace Permafrost\RayScan\Tests\Results;

use Permafrost\RayScan\Results\ScanErrorResult;
use Permafrost\RayScan\Support\File;
use PHPUnit\Framework\TestCase;

class ScanErrorResultTest extends TestCase
{
    /** @test */
    public function it_returns_the_error_message()
    {
        $file = new File(__FILE__);
        $exception = new \Exception('test message');

        $error = new ScanErrorResult($file, $exception, 'test error message');

        $this->assertEquals('test error message', $error->message());
    }
}
