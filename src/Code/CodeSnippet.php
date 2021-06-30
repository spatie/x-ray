<?php

namespace Permafrost\RayScan\Code;

use Permafrost\RayScan\Support\File;
use RuntimeException;

/**
 * Original code from spatie/backtrace
 *
 * @link https://github.com/spatie/backtrace/blob/master/src/CodeSnippet.php
 */
class CodeSnippet
{
    /** @var int */
    protected $surroundingLine = 1;

    /** @var int */
    protected $snippetLineCount = 9;

    /** @var array|string[] */
    protected $code = [];

    public function surroundingLine(int $surroundingLine): self
    {
        $this->surroundingLine = $surroundingLine;

        return $this;
    }

    public function snippetLineCount(int $snippetLineCount): self
    {
        $this->snippetLineCount = $snippetLineCount;

        return $this;
    }

    public function fromFile(string $fileName): self
    {
        if (! file_exists($fileName)) {
            $this->code = [];

            return $this;
        }

        try {
            $file = new File($fileName);

            [$startLineNumber, $endLineNumber] = $this->getBounds($file->numberOfLines());

            $code = [];

            $line = $file->getLine($startLineNumber);

            $currentLineNumber = $startLineNumber;

            while ($currentLineNumber <= $endLineNumber) {
                $code[$currentLineNumber] = rtrim(substr($line, 0, 250));

                $line = $file->getNextLine();
                $currentLineNumber++;
            }

            $this->code = $code;
        } catch (RuntimeException $exception) {
            $this->code = [];
        }

        return $this;
    }

    public function getCode(): array
    {
        return $this->code;
    }

    protected function getBounds(int $totalNumberOfLineInFile): array
    {
        $startLine = max($this->surroundingLine - floor($this->snippetLineCount / 2), 1);

        $endLine = $startLine + ($this->snippetLineCount - 1);

        if ($endLine > $totalNumberOfLineInFile) {
            $endLine = $totalNumberOfLineInFile;
            $startLine = max($endLine - ($this->snippetLineCount - 1), 1);
        }

        return [$startLine, $endLine];
    }
}
