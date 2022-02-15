<?php

namespace Spatie\XRay\Tests\TestClasses;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FakeOutput implements OutputInterface
{
    public $writtenData = [];

    public $formatter;

    public function __construct()
    {
        $this->formatter = new FakeFormatter();
    }

    public function getFormatter(): OutputFormatterInterface
    {
        return $this->formatter;
    }

    public function getVerbosity(): int
    {
        // TODO: Implement getVerbosity() method.
        return 0;
    }

    public function isDebug(): bool
    {
        // TODO: Implement isDebug() method.
        return false;
    }

    public function isDecorated(): bool
    {
        // TODO: Implement isDecorated() method.
        return false;
    }

    public function isQuiet(): bool
    {
        // TODO: Implement isQuiet() method.
        return false;
    }

    public function isVerbose(): bool
    {
        // TODO: Implement isVerbose() method.
        return false;
    }

    public function isVeryVerbose(): bool
    {
        // TODO: Implement isVeryVerbose() method.
        return false;
    }

    public function setDecorated(bool $decorated): void
    {
        // TODO: Implement setDecorated() method.
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        // TODO: Implement setFormatter() method.
    }

    public function setVerbosity(int $level): void
    {
        // TODO: Implement setVerbosity() method.
    }

    public function write($messages, bool $newline = false, int $options = 0): void
    {
        if (count($this->writtenData) === 0) {
            $this->writtenData[] = '';
        }

        $this->writtenData[count($this->writtenData) - 1] .= $this->stripAnsi($messages) . ($newline ? PHP_EOL : '');

        //$this->writtenData[] = $this->stripAnsi($messages);
    }

    public function writeln($messages, int $options = 0): void
    {
        $this->writtenData[] = $this->stripAnsi($messages);
    }

    protected function stripAnsi(string $str): string
    {
        return preg_replace("~\e\[[0-9;]+m~", '', $str);
    }
}
