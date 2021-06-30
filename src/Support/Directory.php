<?php

namespace Permafrost\RayScan\Support;

class Directory
{
    /** @var string */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function files(): array
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

            if (Str::startsWith($name, realpath($this->path) . '/vendor')) {
                continue;
            }

            if (Str::startsWith($name, realpath($this->path) . '/node_modules')) {
                continue;
            }

            if ($file->isFile() && $file->getExtension() === 'php') {
                $result[] = $name;
            }
        }

        return $result;
    }
}
