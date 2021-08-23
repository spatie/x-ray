<?php

namespace Spatie\XRay\Configuration;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Some code borrowed from spatie/ray:
 *
 * @link https://github.com/spatie/ray/blob/main/src/Settings/SettingsFactory.php
 */
class ConfigurationFactory
{
    /** @var array|string[] */
    public static $cache = [];

    public static function create(InputInterface $input, ?string $configDirectory = null): Configuration
    {
        $paths = $input->getArgument('path');

        if (! is_array($paths)) {
            $paths = [$paths];
        }

        $hideProgress = $input->hasOption('no-progress') && $input->getOption('no-progress') === true;
        $showSnippets = $input->hasOption('snippets') && $input->getOption('snippets') === true;
        $showSummary = $input->hasOption('summary') && $input->getOption('summary') === true;
        $compactMode = $input->hasOption('compact') && $input->getOption('compact') === true;
        $verboseMode = $input->hasOption('verbose') && $input->getOption('verbose') === true;
        $ignorePathsOption = $input->hasOption('ignore') ? $input->getOption('ignore') : [];

        $result = new Configuration($paths, $showSnippets, $hideProgress, $showSummary, $compactMode, $verboseMode);
        $options = (new static())->getSettingsFromConfigFile($configDirectory);

        $result->loadOptionsFromConfigurationFile($options, $ignorePathsOption);

        return $result;
    }

    public function getSettingsFromConfigFile(?string $configDirectory = null): array
    {
        $configFilePath = $this->searchConfigFiles($configDirectory);

        if (! file_exists($configFilePath)) {
            return [];
        }

        $options = Yaml::parseFile($configFilePath);

        return $options ?? [];
    }

    protected function searchConfigFiles(?string $configDirectory = null): string
    {
        if (! isset(self::$cache[$configDirectory])) {
            self::$cache[$configDirectory] = $this->searchConfigFilesOnDisk($configDirectory);
        }

        return self::$cache[$configDirectory];
    }

    protected function searchConfigFilesOnDisk(?string $configDirectory = null): string
    {
        $configNames = [
            'x-ray.yml.dist',
            'x-ray.yml',
        ];

        $configDirectory = $configDirectory ?? (string)getcwd();

        while (@is_dir($configDirectory)) {
            foreach ($configNames as $configName) {
                $configFullPath = $configDirectory.DIRECTORY_SEPARATOR.$configName;
                if (file_exists($configFullPath)) {
                    return $configFullPath;
                }
            }

            $parentDirectory = dirname($configDirectory);

            // We do a direct comparison here since there's a difference between
            // the root directories on windows / *nix systems which does not
            // let us compare it against the DIRECTORY_SEPARATOR directly
            if ($parentDirectory === $configDirectory) {
                return '';
            }

            $configDirectory = $parentDirectory;
        }

        return '';
    }
}
