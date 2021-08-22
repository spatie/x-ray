<?php

namespace Spatie\RayScan\Tests\Printers;

use PHPUnit\Framework\TestCase;
use Spatie\RayScan\CodeScanner;
use Spatie\RayScan\Printers\ConsoleResultsPrinter;
use Spatie\RayScan\Tests\TestClasses\FakeConsoleColor;
use Spatie\RayScan\Tests\TestClasses\FakeOutput;
use Spatie\RayScan\Tests\Traits\CreatesTestConfiguration;
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
        $printer->consoleColor = new FakeConsoleColor();
        $scanner = new CodeScanner($config, $path);

        $results = $scanner->scan();
        $printer->printSummary($results);

        $this->assertMatchesSnapshot($output->writtenData);
    }

    /** @test */
    public function it_prints_a_summary_without_a_table()
    {
        $path = __DIR__ . '/../fixtures/fixture1.php';
        $config = $this->createConfiguration([$path], null, ['path' => $path]);
        $output = new FakeOutput();
        $printer = new ConsoleResultsPrinter($output, $config);
        $printer->consoleColor = new FakeConsoleColor();
        $scanner = new CodeScanner($config, $path);

        $results = $scanner->scan();
        $printer->printSummary($results);

        $this->assertMatchesSnapshot($output->writtenData);
    }
}
