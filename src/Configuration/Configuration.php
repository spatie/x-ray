<?php

namespace Permafrost\RayScan\Configuration;

use Permafrost\RayScan\Exceptions\MissingArgumentException;

class Configuration
{
    /** @var array|string[] */
    public $paths;

    /** @var bool */
    public $showSnippets = false;

    /** @var bool */
    public $hideProgress = false;

    /** @var bool */
    public $showSummary = false;

    /** @var bool */
    public $compactMode = false;

    /** @var bool */
    public $verboseMode = false;

    /** @var ConfigurationItemList */
    public $pathnames;

    /** @var ConfigurationItemList */
    public $functions;

    public function __construct(?array $paths, bool $showSnippets, bool $hideProgress, bool $showSummary, bool $compactMode = false, bool $verboseMode = false)
    {
        $this->paths = $paths ?? [];
        $this->showSnippets = $showSnippets;
        $this->hideProgress = $hideProgress;
        $this->showSummary = $showSummary;
        $this->compactMode = $compactMode;
        $this->verboseMode = $verboseMode;

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
                throw new \InvalidArgumentException('Invalid input file or path provided: ' . $path);
            }
        }

        return $this;
    }

    public function isDefaultMode(): bool
    {
        return ! $this->showSummary
            && ! $this->compactMode;
    }

    /**
     * @param array $options
     * @param array $ignorePathsOption
     */
    public function loadOptionsFromConfigurationFile(array $options, array $ignorePathsOption): void
    {
        $this->functions->ignore = array_unique($options['functions']['ignore'] ?? []);
        $this->functions->include = array_unique($options['functions']['include'] ?? []);

        $this->pathnames->ignore = array_unique(array_merge($options['paths']['ignore'] ?? [], $ignorePathsOption));
        $this->pathnames->include = array_unique($options['paths']['include'] ?? []);
    }
}
