<?php

namespace Spatie\XRay\Configuration;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigurationFactory
{
    public static array $cache = [];

    public static function create(InputInterface $input, ?string $configDirectory = null): Configuration
    {
        $paths = $input->getArgument('path');

        if (! is_array($paths)) {
            $paths = [$paths];
        }

        $hideProgress = self::getOption($input, 'no-progress', false);
        $showSnippets = self::getOption($input, 'snippets', false);
        $showSummary = self::getOption($input, 'summary', false);
        $githubAnnotation = self::getOption($input, 'github', false);
        $compactMode = self::getOption($input, 'compact', false);
        $verboseMode = self::getOption($input, 'verbose', false);
        $ignorePaths = self::getOption($input, 'ignore', []);

        $result = new Configuration($paths, $showSnippets, $hideProgress, $showSummary, $githubAnnotation, $compactMode, $verboseMode);
        $options = (new static())->getSettingsFromConfigFile($configDirectory);

        $result->loadOptionsFromConfigurationFile($options, $ignorePaths);

        return $result;
    }

    protected static function getOption(InputInterface $input, string $name, mixed $default): mixed
    {
        if (! $input->hasOption($name)) {
            return $default;
        }

        return $input->getOption($name);
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
