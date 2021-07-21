<?php

namespace Permafrost\RayScan\Support;

class Progress
{
    /** @var int */
    public $total;

    /** @var int */
    public $current;

    /** @var callable | null */
    public $callback = null;

    public function __construct(int $total)
    {
        $this->total = $total;
        $this->current = 0;
    }

    public function advance(int $amount = 1): self
    {
        $this->current += $amount;

        if ($this->callback) {
            call_user_func($this->callback, $this->current, $this->total);
        }

        return $this;
    }

    public function withCallback($callback): self
    {
        $this->callback = $callback;

        return $this;
    }
}
