<?php

namespace Permafrost\RayScan\Tests\Traits;

use Permafrost\RayScan\Configuration\Configuration;
use Permafrost\RayScan\Configuration\ConfigurationFactory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

trait CreatesTestConfiguration
{
    protected function createInput(array $input)
    {
        $inputDefinition = new InputDefinition([
            new InputArgument('path', InputArgument::REQUIRED),
            new InputOption('no-progress', 'P', InputOption::VALUE_NONE),
            new InputOption('snippets', 'S', InputOption::VALUE_NONE),
        ]);

        return new ArrayInput($input, $inputDefinition);
    }

    protected function createConfiguration(string $path, ?string $configPath = null, ?array $options = null): Configuration
    {
        $configPath = $configPath ?? __DIR__ . '/../data';
        $options = $options ?? ['path' => $path, '--no-progress' => true, '--snippets' => false];

        $input = $this->createInput($options);

        return ConfigurationFactory::create($input, $configPath);
    }

    protected function createConfigurationFromInput(InputInterface $input, ?string $configPath = null): Configuration
    {
        $configPath = $configPath ?? __DIR__ . '/../data';

        return ConfigurationFactory::create($input, $configPath);
    }
}
