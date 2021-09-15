<?php

namespace Spatie\XRay\Printers;

use Spatie\XRay\Configuration\Configuration;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ResultsPrinter
{
    /** @var ResultPrinter */
    protected $printer = null;

    public function __construct(
        protected OutputInterface $output,
        protected Configuration $config,
    ) {
        //
    }

    abstract public function print(array $results): void;

    abstract public function printSummary(array $results): void;

    abstract protected function printer(): ResultPrinter;
}
