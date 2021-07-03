<?php

namespace Permafrost\RayScan\Support;

class Progress
{
    /** @var int */
    public $total;

    /** @var int */
    public $current;

    /** @var int */
    public $scale;

    /** @var callable | null */
    public $callback = null;

    public function __construct(int $total, int $scale = 100)
    {
        $this->total = $total;
        $this->scale = $scale;
        $this->current = 0;
    }

    public function advance(int $amount = 1): self
    {
        $this->current += $amount;

        if ($this->callback) {
            $data = ProgressData::create([
                'current' => $this->current,
                'position' => $this->position(),
                'scale' => $this->scale,
                'total' => $this->total,
            ]);

            call_user_func($this->callback, $data);
        }

        return $this;
    }

    public function withCallback($callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    public function position(): int
    {
        return ($this->current / $this->total) * $this->scale;
    }
}
