<?php

namespace Permafrost\RayScan\Configuration;

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

    /** @var array|string[] */
    public $ignoreFunctions = [];

    /** @var array|string[] */
    public $ignorePaths = [];

    public function __construct(?array $paths, bool $showSnippets, bool $hideProgress, bool $showSummary, bool $compactMode = false)
    {
        $this->paths = $paths ?? [];
        $this->showSnippets = $showSnippets;
        $this->hideProgress = $hideProgress;
        $this->showSummary = $showSummary;
        $this->compactMode = $compactMode;
    }

    public function validate(): self
    {
        if (count($this->paths) === 0) {
            throw new \InvalidArgumentException('Please provide an input file or path.');
        }

        foreach($this->paths as $path) {
            if (!file_exists($path)) {
                throw new \InvalidArgumentException('Invalid input file or path provided: ' . $path);
            }
        }

        return $this;
    }

}
