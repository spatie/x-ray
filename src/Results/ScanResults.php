<?php

namespace Permafrost\RayScan\Results;

use Permafrost\RayScan\Code\CodeLocation;
use Permafrost\RayScan\Code\CodeSnippet;
use Permafrost\RayScan\Code\FunctionCallLocation;

class ScanResults
{
    /** @var array|CodeLocation[]|FunctionCallLocation[]  */
    public $results = [];

    public function addFromLocation(CodeLocation $location): self
    {
        $snippet = (new CodeSnippet())
            ->surroundingLine($location->startLine)
            ->snippetLineCount(8)
            ->fromFile($location->filename);

        $this->results[] = new ScanResult($location, $snippet);

        return $this;
    }
}
