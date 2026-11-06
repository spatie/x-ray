<?php

namespace Spatie\XRay\Tests\Configuration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Spatie\XRay\Configuration\ConfigurationFactory;
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

    #[Test]
    public function it_creates_a_configuration_object()
    {
        $path = [realpath(__DIR__.'/../fixtures/fixture1.php')];
        $input = $this->createInput(['path' => $path, '--no-progress' => true, '--snippets' => true]);

        $config = ConfigurationFactory::create($input, __DIR__.'/../data');

        $this->assertTrue($config->showSnippets);
        $this->assertTrue($config->hideProgress);
        $this->assertEquals($path, $config->paths);
    }

    #[Test]
    public function it_loads_and_caches_settings_from_the_current_or_an_explicit_directory(): void
    {
        $directory = sys_get_temp_dir().'/x-ray-'.bin2hex(random_bytes(8));
        mkdir($directory);
        mkdir($directory.'/explicit');
        file_put_contents($directory.'/x-ray.yml', 'functions: {include: [ray]}');
        file_put_contents($directory.'/explicit/x-ray.yml', 'functions: {include: [rd]}');

        $workingDirectory = getcwd();
        $cache = ConfigurationFactory::$cache;
        ConfigurationFactory::$cache = [];

        try {
            chdir($directory);
            $factory = new ConfigurationFactory;

            $this->assertSame(['functions' => ['include' => ['ray']]], $factory->getSettingsFromConfigFile());
            $this->assertSame(['functions' => ['include' => ['rd']]], $factory->getSettingsFromConfigFile($directory.'/explicit'));
            $this->assertSame(['functions' => ['include' => ['ray']]], $factory->getSettingsFromConfigFile());
            $this->assertSame(['functions' => ['include' => ['rd']]], $factory->getSettingsFromConfigFile($directory.'/explicit'));
        } finally {
            chdir($workingDirectory);
            ConfigurationFactory::$cache = $cache;
            unlink($directory.'/explicit/x-ray.yml');
            unlink($directory.'/x-ray.yml');
            rmdir($directory.'/explicit');
            rmdir($directory);
        }
    }

    #[Test]
    public function it_throws_an_exception_when_validating_the_configuration_if_the_filename_does_not_exist()
    {
        $filename = [realpath(__DIR__.'/../fixtures/missing.php')];

        $input = $this->createInput(['path' => $filename, '--no-progress' => true]);
        $config = ConfigurationFactory::create($input, __DIR__.'/../data');

        $this->expectException(\InvalidArgumentException::class);

        $config->validate();
    }

    #[Test]
    public function it_does_not_throw_an_exception_when_validating_the_configuration_if_the_filename_exists()
    {
        $filename = [realpath(__DIR__.'/../fixtures/fixture1.php')];

        $input = $this->createInput(['path' => $filename, '--no-progress' => true]);
        $config = ConfigurationFactory::create($input, __DIR__.'/../data');
        $hasException = false;

        try {
            $config->validate();
        } catch (\Exception $e) {
            $hasException = true;
        }

        $this->assertFalse($hasException);
    }
}
