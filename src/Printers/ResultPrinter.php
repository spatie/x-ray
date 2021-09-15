<?php

namespace Spatie\XRay\Printers;

use Permafrost\PhpCodeSearch\Results\SearchResult;
use Spatie\XRay\Configuration\Configuration;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ResultPrinter
{
    public function __construct(protected Configuration $config)
    {
        //
    }

    abstract public function print(OutputInterface $output, SearchResult $result): void;
}
