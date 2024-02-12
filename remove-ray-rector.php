<?php

use Spatie\XRay\Support\RemoveRayCallRector;

return static function (\Rector\Config\RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveRayCallRector::class);
};
