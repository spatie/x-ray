<?php

namespace Permafrost\RayScan\Results;

use Permafrost\RayScan\Code\CodeLocation;
use Permafrost\RayScan\Code\CodeSnippet;
use Permafrost\RayScan\Code\FunctionCallLocation;
use Permafrost\RayScan\Support\File;

class ScanResults
{
    /** @var array|CodeLocation[]|FunctionCallLocation[]  */
    public $results = [];

    /** @var array|ScanErrorResult[] */
    public $errors = [];

    /** @var File */
    public $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function addFromLocation(CodeLocation $location): self
    {
        $snippet = (new CodeSnippet())
            ->surroundingLine($location->startLine)
            ->snippetLineCount(8)
            ->fromFile($this->file);

        $this->results[] = new ScanResult($location, $snippet);

        return $this;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function addError(ScanErrorResult $errorResult)
    {
        $this->errors[] = $errorResult;

        return $this;
    }
}
