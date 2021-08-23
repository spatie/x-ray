<?php

namespace Spatie\XRay;

use Permafrost\PhpCodeSearch\Results\FileSearchResults;
use Permafrost\PhpCodeSearch\Searcher;
use Spatie\XRay\Configuration\Configuration;
use Symfony\Component\Finder\Finder;

class CodeScanner
{
    /** @var Configuration */
    protected $config;

    /** @var array */
    protected $paths;

    public function __construct(Configuration $config, $path)
    {
        if (! is_array($path)) {
            $path = [$path];
        }

        $this->config = $config;
        $this->paths = $this->loadDirectoryFiles($path);
    }

    public function scanFile(string $file): FileSearchResults
    {
        $searcher = new Searcher();

        return $searcher
            ->functions($this->config->functions->values())
            ->methods(['ray'])
            ->static(['Ray'])
            ->classes(['Ray'])
            ->search($file);
    }

    public function scan(?array $paths = null, ?callable $postScanCallback = null): array
    {
        $paths = $paths ?? $this->paths();

        $results = [];

        foreach ($paths as $path) {
            if ($this->isPathIgnored($path)) {
                continue;
            }

            $scanResults = $this->scanFile($path);

            if ($postScanCallback) {
                $postScanCallback($path, $scanResults);
            }

            if (! $scanResults) {
                continue;
            }

            if ($scanResults->hasErrors()) {
                // TODO: handle scan errors
            }

            if (! $scanResults->hasErrors() && count($scanResults->results)) {
                $results[] = $scanResults;
            }
        }

        return $results;
    }

    public function paths(): array
    {
        return $this->paths;
    }

    protected function loadDirectoryFiles(array $paths): array
    {
        $result = [];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $result[] = realpath($path);

                continue;
            }

            $finder = Finder::create()
                ->followLinks()
                ->ignoreDotFiles(true)
                ->ignoreVCS(true)
                ->ignoreVCSIgnored($path === getcwd() && file_exists("{$path}/.gitignore"))
                ->ignoreUnreadableDirs(true)
                ->in(array_unique(array_merge([$path], $this->config->pathnames->included())))
                ->name(array_unique($this->config->pathnames->included()))
                ->notName($this->config->pathnames->ignored())
                ->exclude($this->config->pathnames->ignored())
                ->exclude('vendor')
                ->exclude('node_modules')
                ->name('*.php')
                ->files();

            foreach ($finder as $file) {
                $result[] = $file;
            }
        }

        sort($result);

        return $result;
    }

    protected function isPathIgnored(string $path): bool
    {
        if (in_array($path, $this->config->pathnames->included(), true)) {
            return false;
        }

        if (in_array(basename($path), $this->config->pathnames->included(), true)) {
            return false;
        }

        if (in_array($path, $this->config->pathnames->ignored(), true)) {
            return true;
        }

        if (in_array(basename($path), $this->config->pathnames->ignored(), true)) {
            return true;
        }

        foreach ($this->config->pathnames->included() as $pathname) {
            $pathname = str_replace(['*', '?', '~'], ['.*', '.', '\\~'], $pathname);

            if (preg_match('~' . $pathname . '~', $path) === 1) {
                return false;
            }
        }

        foreach ($this->config->pathnames->ignored() as $pathname) {
            $pathname = str_replace(['*', '?', '~'], ['.*', '.', '\\~'], $pathname);

            if (preg_match('~' . $pathname . '~', $path) === 1) {
                return true;
            }
        }

        return false;
    }
}
