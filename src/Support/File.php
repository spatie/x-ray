<?php

namespace Permafrost\RayScan\Support;

use SplFileObject;

/**
 * Original code taken from spatie/backtrace
 *
 * @link https://github.com/spatie/backtrace/blob/master/src/File.php
 */
class File
{
    /** @var \SplFileObject */
    protected $file = null;

    /** @var string */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function file(): SplFileObject
    {
        if (! $this->file) {
            $this->file = new SplFileObject($this->path);
        }

        return $this->file;
    }

    public function exists(): bool
    {
        return file_exists($this->getRealPath());
    }

    public function contents(): string
    {
        return file_get_contents($this->path);
    }

    public function getRealPath(): string
    {
        return realpath($this->path);
    }

    public function numberOfLines(): int
    {
        $this->file()->seek(PHP_INT_MAX);

        return $this->file()->key() + 1;
    }

    public function getLine(int $lineNumber = null): string
    {
        if ($lineNumber === null) {
            return $this->getNextLine();
        }

        $this->file()->seek($lineNumber - 1);

        return $this->file()->current();
    }

    public function getNextLine(): string
    {
        $this->file()->next();

        return $this->file()->current();
    }
}
