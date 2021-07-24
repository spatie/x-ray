<?php

namespace Permafrost\RayScan\Tests\Printers;

use Permafrost\RayScan\CodeScanner;
use Permafrost\RayScan\Printers\ConsoleResultsPrinter;
use Permafrost\RayScan\Tests\TestClasses\FakeOutput;
use Permafrost\RayScan\Tests\Traits\CreatesTestConfiguration;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ConsoleResultsPrinterTest extends TestCase
{
    use CreatesTestConfiguration;
    use MatchesSnapshots;

    /** @test */
    public function it_prints_a_summary_with_a_table()
    {
        $path = __DIR__ . '/../fixtures/fixture1.php';
        $config = $this->createConfiguration($path, null, ['path' => $path, '--summary' => true]);
        $output = new FakeOutput();
        $printer = new ConsoleResultsPrinter($output, $config);
        $scanner = new CodeScanner($config, $path);

        $results = $scanner->scan();
        $printer->printSummary($results);

        $this->assertMatchesSnapshot($output->writtenData);
    }

    /** @test */
    public function it_prints_a_summary_without_a_table()
    {
        $path = __DIR__ . '/../fixtures/fixture1.php';
        $config = $this->createConfiguration($path, null, ['path' => $path]);
        $output = new FakeOutput();
        $printer = new ConsoleResultsPrinter($output, $config);
        $scanner = new CodeScanner($config, $path);

        $results = $scanner->scan();
        $printer->printSummary($results);

        $this->assertMatchesSnapshot($output->writtenData);
    }
}
