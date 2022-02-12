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

    public function getFormatter()
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
        return false;
    }

    public function isDecorated(): bool
    {
        return false;
    }

    public function isQuiet(): bool
    {
        return false;
    }

    public function isVerbose(): bool
    {
        return false;
    }

    public function isVeryVerbose(): bool
    {
        return false;
    }

    public function setDecorated(bool $decorated)
    {
        // TODO: Implement setDecorated() method.
    }

    public function setFormatter(OutputFormatterInterface $formatter)
    {
        // TODO: Implement setFormatter() method.
    }

    public function setVerbosity(int $level)
    {
        // TODO: Implement setVerbosity() method.
    }

    public function write($messages, bool $newline = false, int $options = 0)
    {
        if (count($this->writtenData) === 0) {
            $this->writtenData[] = '';
        }

        $this->writtenData[count($this->writtenData) - 1] .= $this->stripAnsi($messages) . ($newline ? PHP_EOL : '');

        //$this->writtenData[] = $this->stripAnsi($messages);
    }

    public function writeln($messages, int $options = 0)
    {
        $this->writtenData[] = $this->stripAnsi($messages);
    }

    protected function stripAnsi(string $str): string
    {
        return preg_replace("~\e\[[0-9;]+m~", '', $str);
    }
}
