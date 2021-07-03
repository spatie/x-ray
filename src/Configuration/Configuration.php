<?php

namespace Permafrost\RayScan\Configuration;

class Configuration
{
    /** @var string */
    public $path;

    /** @var bool */
    public $hideSnippets = false;

    /** @var bool */
    public $hideProgress = false;

    public function __construct(string $path, bool $hideSnippets, bool $hideProgress)
    {
        $this->path = $path;
        $this->hideSnippets = $hideSnippets;
        $this->hideProgress = $hideProgress;
    }

    public function validate(): void
    {
        if (! file_exists($this->filename)) {
            throw new \InvalidArgumentException('Invalid input file or path provided.');
        }
    }
}
