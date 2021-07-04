<?php

namespace Permafrost\RayScan;

use Permafrost\PhpCodeSearch\Results\FileSearchResults;
use Permafrost\PhpCodeSearch\Searcher;
use Permafrost\PhpCodeSearch\Support\File;
use Permafrost\RayScan\Configuration\Configuration;

class CodeScanner
{
    /** @var Configuration */
    protected $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    public function scan(File $file): FileSearchResults
    {
        $searcher = new Searcher();

        return $searcher
            ->functions(['ray', 'rd'])
            ->static(['Ray'])
            ->classes(['Ray'])
            ->search($file);
    }
}
