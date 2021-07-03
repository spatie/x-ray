<?php

namespace Permafrost\RayScan\Concerns;

trait HasPaths
{
    protected function initializePaths(): self
    {
        $this->paths = $this->getPaths($this->config->path);

        return $this;
    }

    protected function getPaths(string $path): array
    {
        $paths = [];

        if (is_dir($path)) {
            $paths = $this->loadDirectoryFiles($path);
        }

        if (!is_dir($path) && is_file($path)) {
            $paths = $this->loadFile($path);
        }

        return $paths;
    }
}
