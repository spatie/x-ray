<?php

namespace Spatie\XRay\Configuration;

use InvalidArgumentException;
use Spatie\XRay\Exceptions\MissingArgumentException;

class Configuration
{
    public ConfigurationItemList $pathnames;

    public ConfigurationItemList $functions;

    public function __construct(
        public ?array $paths,
        public bool $showSnippets,
        public bool $hideProgress,
        public bool $showSummary,
        public bool $compactMode = false,
        public bool $verboseMode = false,
    ) {
        $this->paths = $paths ?? [];

        if ($this->verboseMode) {
            $this->hideProgress = true;
        }

        $this->functions = ConfigurationItemList::make(['ray', 'rd']);
        $this->pathnames = new ConfigurationItemList();
    }

    public function validate(): self
    {
        if (count($this->paths) === 0) {
            throw MissingArgumentException::make('Please provide an input file or path.');
        }

        foreach ($this->paths as $path) {
            if (! file_exists($path)) {
                throw new InvalidArgumentException('Invalid input file or path provided: ' . $path);
            }
        }

        return $this;
    }

    public function isDefaultMode(): bool
    {
        return ! $this->showSummary
            && ! $this->compactMode;
    }

    public function loadOptionsFromConfigurationFile(array $options, array $ignorePathsOption): void
    {
        $this->functions->ignore = array_unique($options['functions']['ignore'] ?? []);
        $this->functions->include = array_unique($options['functions']['include'] ?? []);

        $this->pathnames->ignore = array_unique(array_merge($options['paths']['ignore'] ?? [], $ignorePathsOption));
        $this->pathnames->include = array_unique($options['paths']['include'] ?? []);
    }
}
