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

            if (Str::startsWith($file->getFilename(), '.')) {
                continue;
            }

            if (Str::startsWith($name, $this->path . '/vendor')) {
                continue;
            }

            if (Str::startsWith($name, $this->path . '/node_modules')) {
                continue;
            }

            if ($file->isFile() && $file->isReadable() && $file->getExtension() === 'php') {
                $result[] = $name;
            }
        }

        $this->files = array_unique($result);

        return $this;
    }

    public function only(string ...$filenames): array
    {
        return array_filter($this->files(), function($filename) use ($filenames) {
            return in_array($filename, $filenames, true);
        });
    }

    public function files(): array
    {
        return $this->files;
    }
}
