<?php

namespace Permafrost\RayScan\Printers;

use Permafrost\RayScan\Configuration\Configuration;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ResultsPrinter
{
    abstract public function print(OutputInterface $output, array $results, Configuration $config): void;

    abstract public function printSummary(OutputInterface $output, array $results, Configuration $config): void;
}
