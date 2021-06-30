<?php

namespace Permafrost\RayScan\Tests\Support;

use Permafrost\RayScan\Support\Directory;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    /** @test */
    public function it_gets_all_php_files_from_a_directory_recursively()
    {
        $dir = new Directory(__DIR__ . '/../fixtures');

        $this->assertCount(3, $dir->load()->files());
    }

    /** @test */
    public function it_gets_only_specific_php_files_from_a_directory_recursively()
    {
        $dir = new Directory(__DIR__ . '/../fixtures');
        $filename = realpath(__DIR__ . '/../fixtures/fixture1.php');

        $this->assertCount(1, $dir->load()->only($filename));
    }
}
