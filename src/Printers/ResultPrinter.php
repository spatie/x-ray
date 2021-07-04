<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\PhpCodeSearch\Results\SearchResult;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ResultPrinter
{
    abstract public function print(OutputInterface $output, SearchResult $result, bool $colorize = true);
}
