<?php

namespace Permafrost\RayScan\Printers;

class MessagePrinter
{
    public static function status($output, string $status, string $indent = ' '): void
    {
        $output->writeln("{$indent}<fg=#3B82F6>❱</> {$status}");
    }

    public static function success($output, string $message, string $indent = ' '): void
    {
        $output->writeln("{$indent}<fg=#169b3c>✔</> {$message}");
    }

    public static function failure($output, string $message, string $indent = ' '): void
    {
        $output->writeln("{$indent}<fg=red;options=bold>✗</> {$message}");
    }

    public static function warning($output, string $message, string $indent = ' '): void
    {
        $output->writeln("{$indent}<fg=#ef4444>❗</>{$message}");
    }
}