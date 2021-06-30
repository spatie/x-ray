<?php

namespace Permafrost\RayScan\Results;

use Permafrost\RayScan\Support\File;

class ScanErrorResult
{
    /** @var File */
    public $file;

    /** @var \Exception */
    public $error;

    /** @var string */
    protected $message;

    public function __construct(File $file, \Exception $error, string $message)
    {
        $this->file = $file;
        $this->error = $error;
        $this->message = $message;
    }

    public function message(): string
    {
        return $this->message;
    }
}
