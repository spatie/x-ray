<?php

namespace Permafrost\RayScan\Results;

use Permafrost\RayScan\Code\CodeLocation;
use Permafrost\RayScan\Code\CodeSnippet;
use Permafrost\RayScan\Code\FunctionCallLocation;

class ScanResult
{
    /** @var CodeLocation|FunctionCallLocation */
    public $location;

    /** @var CodeSnippet */
    public $snippet;

    public function __construct(CodeLocation $location, CodeSnippet $snippet)
    {
        $this->location = $location;
        $this->snippet = $snippet;
    }
}
