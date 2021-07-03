<?php

namespace Permafrost\RayScan\Configuration;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Some code borrowed from spatie/ray:
 *
 * @link https://github.com/spatie/ray/blob/main/src/Settings/SettingsFactory.php
 */
class ConfigurationFactory
{
    public static $cache = [];

    public static function create(InputInterface $input, ?string $configPath = null): Configuration
    {
        $path = $input->getArgument('path');

        $hideProgress = $input->hasOption('no-progress') && $input->getOption('no-progress') === true;
        $hideSnippets = $input->hasOption('no-snippets') && $input->getOption('no-snippets') === true;

        $result = new Configuration($path, $hideSnippets, $hideProgress);

        $options = (new static())->getSettingsFromConfigFile($configPath);

        $result->ignorePaths = $options['ignore']['paths'] ?? [];
        $result->ignoreFunctions = $options['ignore']['functions'] ?? [];

        return $result;
    }

    public function getSettingsFromConfigFile(string $configDirectory = null): array
    {
        $configFilePath = $this->searchConfigFiles($configDirectory);

        if (! file_exists($configFilePath)) {
            return [];
        }

        $options = include $configFilePath;

        return $options ?? [];
    }

    protected function searchConfigFiles(string $configDirectory = null): string
    {
        if (! isset(self::$cache[$configDirectory])) {
            self::$cache[$configDirectory] = $this->searchConfigFilesOnDisk($configDirectory);
        }

        return self::$cache[$configDirectory];
    }

    protected function searchConfigFilesOnDisk(string $configDirectory = null): string
    {
        $configNames = [
            'ray-scan.php',
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
