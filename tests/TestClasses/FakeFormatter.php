<?php

namespace Spatie\XRay\Tests\TestClasses;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

class FakeFormatter extends OutputFormatter
{
    public $styles = [];

    public function setStyle(string $name, OutputFormatterStyleInterface $style): void
    {
        $this->styles[$name] = $style;
    }
}
