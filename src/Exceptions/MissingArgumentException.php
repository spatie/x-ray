<?php

namespace Spatie\XRay\Exceptions;

class MissingArgumentException extends \Exception
{
    public static function make(string $message): self
    {
        return new static($message);
    }
}
