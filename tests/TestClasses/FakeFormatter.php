<?php

namespace Spatie\XRay\Tests\TestClasses;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

class FakeFormatter implements OutputFormatterInterface
{
    public $styles = [];

    public function setDecorated(?bool $decorated)
    {
        // TODO: Implement setDecorated() method.
    }

    public function isDecorated()
    {
        // TODO: Implement isDecorated() method.
    }

    public function format(?string $message)
    {
        // TODO: Implement format() method.
    }

    public function getStyle(string $name)
    {
        return $this->styles[$name] ?? null;
    }

    public function hasStyle(string $name)
    {
        return isset($this->styles[$name]);
    }

    public function setStyle(string $name, OutputFormatterStyleInterface $style)
    {
        $this->styles[$name] = $style;
    }
}
