<?php

namespace Permafrost\RayScan\Tests\Configuration;

use Permafrost\RayScan\Configuration\ConfigurationFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class ConfigurationFactoryTest extends TestCase
{
    protected function createInput(array $input)
    {
        $inputDefinition = new InputDefinition([
            new InputArgument('path', InputArgument::IS_ARRAY),
            new InputOption('no-progress', 'P', InputOption::VALUE_NONE),
            new InputOption('snippets', 'S', InputOption::VALUE_NONE),
        ]);

        return new ArrayInput($input, $inputDefinition);
    }

    /** @test */
    public function it_creates_a_configuration_object()
    {
        $path = realpath(__DIR__.'/../fixtures/fixture1.php');
        $input = $this->createInput(['path' => [$path], '--no-progress' => true, '--snippets' => true]);

        $config = ConfigurationFactory::create($input, __DIR__ . '/../data');

        $this->assertTrue($config->showSnippets);
        $this->assertTrue($config->hideProgress);
        $this->assertEquals($path, $config->path);
    }

    /** @test */
    public function it_throws_an_exception_when_validating_the_configuration_if_the_filename_does_not_exist()
    {
        $filename = realpath(__DIR__.'/../fixtures/missing.php');

        $input = $this->createInput(['path' => [$filename], '--no-progress' => true]);
        $config = ConfigurationFactory::create($input, __DIR__ . '/../data');

        $this->expectException(\InvalidArgumentException::class);

        $config->validate();
    }

    /** @test */
    public function it_does_not_throw_an_exception_when_validating_the_configuration_if_the_filename_exists()
    {
        $filename = realpath(__DIR__.'/../fixtures/fixture1.php');

        $input = $this->createInput(['path' => [$filename], '--no-progress' => true]);
        $config = ConfigurationFactory::create($input, __DIR__ . '/../data');
        $hasException = false;

        try {
            $config->validate();
        } catch(\Exception $e) {
            $hasException = true;
        }

        $this->assertFalse($hasException);
    }
}
