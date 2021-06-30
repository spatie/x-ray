<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\RayScan\Results\ScanResult;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ResultPrinter
{
    abstract public function print(OutputInterface $output, ScanResult $result, bool $colorize = true);
}
