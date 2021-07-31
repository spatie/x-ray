<?php

namespace Permafrost\RayScan\Tests\Commands;

use Permafrost\RayScan\Commands\ScanCommand;
use Permafrost\RayScan\Tests\TestClasses\FakeConsoleColor;
use Permafrost\RayScan\Tests\TestClasses\FakeConsoleResultsPrinter;
use Permafrost\RayScan\Tests\TestClasses\FakeOutput;
use Permafrost\RayScan\Tests\Traits\CreatesTestConfiguration;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class ScanCommandTest extends TestCase
{
    use CreatesTestConfiguration;
    use MatchesSnapshots;

    /** @var ScanCommand */
    protected $command;

    /** @var FakeOutput */
    protected $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new ScanCommand('scan');
        $this->output = new FakeOutput();
    }

    protected function createInput(array $input): ArrayInput
    {
        $inputDefinition = new InputDefinition([
            new InputArgument('path', InputArgument::REQUIRED),
            new InputOption('no-progress', 'P', InputOption::VALUE_NONE),
            new InputOption('snippets', 'S', InputOption::VALUE_NONE),
            new InputOption('ignore', 'i', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
        ]);

        return new ArrayInput($input, $inputDefinition);
    }

    /** @test */
    public function it_executes_the_command_with_a_valid_filename()
    {
        $input = $this->createInput(['path' => __DIR__ . '/../fixtures/fixture1.php', '--no-progress' => true, '--snippets' => true]);

        $this->command->printer = new FakeConsoleResultsPrinter($this->createConfigurationFromInput($input));
        $this->command->printer->consoleColor = new FakeConsoleColor();

        $this->command->execute($input, $this->output);

        $this->assertMatchesSnapshot($this->output->writtenData);
    }

    /** @test */
    public function it_executes_the_command_with_a_valid_path()
    {
        $path = [__DIR__ . '/../fixtures'];
        $input = $this->createInput(['path' => $path, '--no-progress' => true, '--snippets' => true]);

        $this->command->printer = new FakeConsoleResultsPrinter($this->createConfigurationFromInput($input));
        $this->command->printer->consoleColor = new FakeConsoleColor();

        $this->command->execute($input, $this->output);

        $this->assertMatchesSnapshot($this->output->writtenData);
    }

    /** @test */
    public function it_executes_the_command_with_a_valid_path_and_ignores_the_specified_files()
    {
        $path = [__DIR__ . '/../fixtures'];
        $input = $this->createInput(['path' => $path, '--no-progress' => true, '--snippets' => false, '--ignore' => ['fixture*.php']]);

        $this->command->printer = new FakeConsoleResultsPrinter($this->createConfigurationFromInput($input));
        $this->command->printer->consoleColor = new FakeConsoleColor();


        $this->command->execute($input, $this->output);

        $this->assertCount(1, $this->command->scanner->paths());
        $this->assertMatchesSnapshot($this->output->writtenData);
    }

    /** @test */
    public function it_executes_and_has_no_scan_results_and_returns_a_success_exit_code()
    {
        $this->command->printer = new FakeConsoleResultsPrinter($this->createConfiguration(__DIR__ . '/../fixtures/fixture1.php'));

        $input = $this->createInput(['path' => __DIR__ . '/../fixtures/fixture3.php', '--no-progress' => true]);

        $result = $this->command->execute($input, $this->output);

        $this->assertEquals(Command::SUCCESS, $result);
    }

    /** @test */
    public function it_executes_the_command_with_a_valid_filename_and_displays_progress()
    {
        $input = $this->createInput(['path' => __DIR__ . '/../fixtures/fixture1.php']);
        $this->command->printer = new FakeConsoleResultsPrinter($this->createConfiguration(__DIR__ . '/../fixtures/fixture1.php'));

        $this->command->execute($input, $this->output);

        $this->assertMatchesSnapshot($this->output->writtenData);
    }

}
