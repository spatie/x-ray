<?php

namespace Permafrost\RayScan\Support;

class Directory
{
    /** @var string */
    protected $path;

    /** @var array|string[] */
    protected $files = [];

    public function __construct(string $path)
    {
        $this->path = realpath($path);
    }

    public function load(): self
    {
        $result = [];

        $dir = new \RecursiveDirectoryIterator($this->path);
        $files = new \RecursiveIteratorIterator($dir);

        /** @var \SplFileInfo $file */
        foreach($files as $file){
            $name = $file->getRealPath();

            if ($this->isIgnoredPath($name)) {
                continue;
            }

            if ($file->isFile() && $file->getExtension() === 'php') {
                $result[] = $name;
            }
        }

        $this->setFiles($result);

        return $this;
    }

    public function only(string ...$filenames): array
    {
        return array_filter($this->files, function($filename) use ($filenames) {
            return in_array($filename, $filenames, true);
        });
    }

    public function files(): array
    {
        return $this->files;
    }

    protected function setFiles(array $files): void
    {
        $this->files = array_unique($files);

        sort($this->files);
    }

    protected function isIgnoredPath(string $name): bool
    {
        if (Str::startsWith(basename($name), '.')) {
            return true;
        }

        if (Str::startsWith($name, "{$this->path}/vendor")) {
            return true;
        }

        if (Str::startsWith($name, "{$this->path}/node_modules")) {
            return true;
        }

        return false;
    }
}
