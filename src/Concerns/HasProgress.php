<?php

namespace Permafrost\RayScan\Concerns;

use Permafrost\RayScan\Support\Progress;
use Permafrost\RayScan\Support\ProgressData;

trait HasProgress
{
    protected function initializeProgress($paths = null): self
    {
        $paths = $paths ?? $this->paths;

        $this->progress = new Progress(count($paths), count($paths));

        if (! $this->config->hideProgress) {
            $this->style->progressStart(count($paths));

            $this->progress->withCallback(function (ProgressData $data) {
                usleep(10000);
                $this->style->progressAdvance($data->position);
            });
        }

        return $this;
    }

    protected function finalizeProgress(): self
    {
        if (! $this->config->hideProgress) {
            $this->style->progressFinish();
        }

        return $this;
    }
}
