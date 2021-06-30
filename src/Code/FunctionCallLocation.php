<?php

namespace Permafrost\RayScan\Code;

class FunctionCallLocation extends CodeLocation
{
    /** @var string $name */
    public $name;

    public function __construct(string $name, string $filename, int $startLine, int $endLine)
    {
        parent::__construct($filename, $startLine, $endLine);

        $this->name = $name;
    }

    public static function create(string $name, string $filename, int $startLine, int $endLine): self
    {
        return new static($name, $filename, $startLine, $endLine);
    }
}
