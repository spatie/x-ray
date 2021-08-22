<?php

namespace Spatie\XRay\Tests\Traits;

use Spatie\XRay\Configuration\Configuration;
use Spatie\XRay\Configuration\ConfigurationFactory;
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
            new InputArgument('path', InputArgument::IS_ARRAY),
            new InputOption('no-progress', 'P', InputOption::VALUE_NONE),
            new InputOption('snippets', 'S', InputOption::VALUE_NONE),
            new InputOption('summary', 's', InputOption::VALUE_NONE),
            new InputOption('verbose', 'v', InputOption::VALUE_NONE),
        ]);

        return new ArrayInput($input, $inputDefinition);
    }

    protected function createConfiguration($path, ?string $configPath = null, ?array $options = null): Configuration
    {
        if (! is_array($path)) {
            $path = [$path];
        }

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
