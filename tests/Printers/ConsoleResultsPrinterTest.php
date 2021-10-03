<?php

namespace Spatie\XRay\Tests\Printers;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\XRay\CodeScanner;
use Spatie\XRay\Printers\ConsoleResultsPrinter;
use Spatie\XRay\Tests\TestClasses\FakeConsoleColor;
use Spatie\XRay\Tests\TestClasses\FakeOutput;
use Spatie\XRay\Tests\Traits\CreatesTestConfiguration;

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

    /** @test */
    public function it_prints_a_summary_with_github_annotation()
    {
        $path = __DIR__ . '/../fixtures/fixture1.php';
        $config = $this->createConfiguration([$path], null, ['path' => $path, '--github' => true]);
        $output = new FakeOutput();
        $printer = new ConsoleResultsPrinter($output, $config);
        $printer->consoleColor = new FakeConsoleColor();
        $scanner = new CodeScanner($config, $path);

        $results = $scanner->scan();
        $printer->printSummary($results);

        $this->assertMatchesSnapshot($output->writtenData);
    }
}
