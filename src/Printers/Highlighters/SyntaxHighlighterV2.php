<?php

namespace Permafrost\RayScan\Printers\Highlighters;

use Permafrost\CodeSnippets\Bounds;
use Permafrost\CodeSnippets\CodeSnippet;
use Permafrost\RayScan\Support\Str;

/**
 * Original code taken from nunomaduro/collision
 * @link https://github.com/nunomaduro/collision/blob/stable/src/Highlighter.php
 */
class SyntaxHighlighterV2
{
    public const TOKEN_COMMENT    = 'token_comment';
    public const TOKEN_DEFAULT    = 'token_default';
    public const TOKEN_HTML       = 'token_html';
    public const TOKEN_KEYWORD    = 'token_keyword';
    public const TOKEN_STRING     = 'token_string';
    public const TOKEN_VARIABLE   = 'token_variable';
    public const ACTUAL_LINE_MARK = 'actual_line_mark';
    public const LINE_NUMBER      = 'line_number';

    protected const ARROW_SYMBOL_UTF8   = ' ❱❱';//➜';
    protected const DELIMITER_UTF8      = '▕ ';
    protected const LINE_NUMBER_DIVIDER = 'line_divider';
    protected const MARKED_LINE_NUMBER  = 'marked_line';
    protected const WIDTH               = 4;
    protected const TARGET_LINE = 'target_line';

    /**
     * Holds the theme.
     *
     * @var array
     */
    protected const THEME = [
        self::TOKEN_STRING  => ['color_70'],
        self::TOKEN_VARIABLE => ['color_141'],
        self::TOKEN_COMMENT => ['dark_gray', 'italic'],
        self::TOKEN_KEYWORD => ['color_208'],
        self::TOKEN_DEFAULT => ['default'],
        self::TOKEN_HTML    => ['blue', 'bold'],

        self::ACTUAL_LINE_MARK    => ['red', 'bold'],
        self::LINE_NUMBER         => ['dark_gray'],
        self::MARKED_LINE_NUMBER  => ['italic', 'bold'],
        self::LINE_NUMBER_DIVIDER => ['dark_gray'],
        self::TARGET_LINE => ['bold', 'italic'], //'bg_color_25'],
    ];

    /** @var ConsoleColor */
    protected $color;

    protected const DEFAULT_THEME = [
        self::TOKEN_STRING  => 'red',
        self::TOKEN_COMMENT => 'yellow',
        self::TOKEN_KEYWORD => 'green',
        self::TOKEN_DEFAULT => 'default',
        self::TOKEN_HTML    => 'cyan',

        self::ACTUAL_LINE_MARK    => 'dark_gray',
        self::LINE_NUMBER         => 'dark_gray',
        self::MARKED_LINE_NUMBER  => 'dark_gray',
        self::LINE_NUMBER_DIVIDER => 'dark_gray',
    ];

    protected $delimiter = self::DELIMITER_UTF8;

    protected $arrow = self::ARROW_SYMBOL_UTF8;

    protected const NO_MARK = '    ';

    public $lines = [];

    protected $hasOpenTag = false;

    public function highlightSnippet(CodeSnippet $snippet, Bounds $bounds): string
    {
        $code = $snippet->getLines();
        $codeStr = '';

        foreach($code as $line) {
            $codeStr .= $line . PHP_EOL;
        }

        return $this->highlight($codeStr, $bounds, array_keys($code));
    }

    public function __construct(?ConsoleColor $color = null)
    {
        $this->color = $color ?? new ConsoleColor();

        foreach (self::DEFAULT_THEME as $name => $styles) {
            if (!$this->color->hasTheme($name)) {
                $this->color->addTheme($name, $styles);
            }
        }

        foreach (self::THEME as $name => $styles) {
            $this->color->addTheme($name, $styles);
        }

        $this->delimiter .= ' ';
    }

    public function highlight(string $content, Bounds $bounds, array $lineNumbers): string
    {
        return $this->getCodeSnippet($content, $bounds, $lineNumbers);
    }

    public function getCodeSnippet(string $source, Bounds $bounds, array $lineNumbers = []): string
    {
        $tempTokenLines = $this->getHighlightedLines($source);
        $tokenLines = [];
        $index = 0;

        foreach($tempTokenLines as $line) {
            if (isset($lineNumbers[$index])) {
                $tokenLines[$lineNumbers[$index]] = $line;
            }
            $index++;
        }

        $lines = $this->colorLines($tokenLines, $bounds);

        return $this->lineNumbers($lines, range($bounds->start, $bounds->end));
    }

    protected function getHighlightedLines(string $source): array
    {
        $source = str_replace(["\r\n", "\r"], "\n", $source);

        $this->hasOpenTag = Str::startsWith(ltrim($source), '<?php');

        $tokens = $this->tokenize($source);

        return $this->splitToLines($tokens);
    }

    protected function tokenize(string $source): array
    {
        if (! $this->hasOpenTag) {
            $source = "<?"."php {$source}";
        }

        $tokens = token_get_all($source);

        $output      = [];
        $currentType = null;
        $buffer      = '';

        foreach ($tokens as $token) {
            if (is_array($token)) {
                switch ($token[0]) {
                    case T_WHITESPACE:
                        break;


                    case T_OPEN_TAG:
                        // mark the token as a keyword if it wasn't added automatically for tokenization
                        $newType = $this->hasOpenTag ? self::TOKEN_KEYWORD : self::TOKEN_DEFAULT;

                        break;

                    case T_OPEN_TAG_WITH_ECHO:
                    case T_CLOSE_TAG:
                    case T_STRING:
                        // Constants
                    case T_DIR:
                    case T_FILE:
                    case T_METHOD_C:
                    case T_DNUMBER:
                    case T_LNUMBER:
                    case T_NS_C:
                    case T_LINE:
                    case T_CLASS_C:
                    case T_FUNC_C:
                    case T_TRAIT_C:
                        $newType = self::TOKEN_DEFAULT;
                        break;

                    case T_VARIABLE:
                        $newType = self::TOKEN_VARIABLE;
                        break;

                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        $newType = self::TOKEN_COMMENT;
                        break;

                    case T_ENCAPSED_AND_WHITESPACE:
                    case T_CONSTANT_ENCAPSED_STRING:
                        $newType = self::TOKEN_STRING;
                        break;

                    case T_INLINE_HTML:
                        $newType = self::TOKEN_HTML;
                        break;

                    default:
                        $newType = self::TOKEN_KEYWORD;
                }
            } else {
                $newType = $token === '"' ? self::TOKEN_STRING : self::TOKEN_KEYWORD;
            }

            if ($newType === null) {
                continue;
            }

            if ($currentType === null) {
                $currentType = $newType;
            }

            if ($currentType !== $newType) {
                $output[]    = [$currentType, $buffer];
                $buffer      = '';
                $currentType = $newType;
            }

            $buffer .= is_array($token) ? $token[1] : $token;
        }

        if (isset($newType)) {
            $output[] = [$newType, $buffer];
        }

        return $output;
    }

    protected function splitToLines(array $tokens): array
    {
        $lines = [];
        $line = [];

        foreach ($tokens as $token) {
            foreach (explode("\n", $token[1]) as $count => $tokenLine) {
                if ($count > 0) {
                    $lines[] = $line;
                    $line    = [];
                }

                if ($tokenLine === '') {
                    continue;
                }

                $line[] = [$token[0], $tokenLine];
            }
        }

        $lines[] = $line;

        return $lines;
    }

    protected function colorLines(array $tokenLines, Bounds $bounds): array
    {
        $lines = [];
        $firstLineNum = array_keys($tokenLines)[0];

        foreach ($tokenLines as $lineCount => $tokenLine) {
            $line = '';

            foreach ($tokenLine as $token) {
                [$tokenType, $tokenValue] = $token;

                if ($this->color->hasTheme($tokenType)) {
                    $appendStyles = $this->getColorLineAdditionalStyles($lineCount, range($bounds->start, $bounds->end));
                    $line .= $this->color->apply($tokenType, $tokenValue, $appendStyles);
                } else {
                    $line .= $tokenValue;
                }
            }

            // strip the open tag that was added for tokenization
            if ($lineCount === $firstLineNum && ! $this->hasOpenTag) {
                $line = preg_replace('~<\?php\s?~', '', $line);
            }

            $lines[$lineCount] = $line;
        }

        return $lines;
    }

    protected function getColorLineAdditionalStyles(int $currentLine, array $targetLines): array
    {
        if (! in_array($currentLine, $targetLines, true)) {
            return [];
        }

        return self::THEME[self::TARGET_LINE];
    }

    protected function lineNumbers(array $lines, ?array $markLines = null): string
    {
        $lineStrlen = strlen((string) (array_key_last($lines) + 1));
        $lineStrlen = $lineStrlen < self::WIDTH ? self::WIDTH : $lineStrlen;
        $snippet    = '';
        $mark = str_pad($this->arrow . ' ', 4, ' ', STR_PAD_LEFT);

        foreach ($lines as $lineNum => $line) {
            $coloredLineNumber = $this->coloredLineNumber(self::LINE_NUMBER, $lineNum, $lineStrlen);

            if (! empty($markLines)) {
                $isMarkedLine = in_array($lineNum, $markLines, true);

                if (! $isMarkedLine) {
                    $snippet .= self::NO_MARK;
                }

                if ($isMarkedLine) {
                    $snippet .= $this->color->apply(self::ACTUAL_LINE_MARK, $mark);
                    $coloredLineNumber = $this->coloredLineNumber(self::MARKED_LINE_NUMBER, $lineNum, $lineStrlen);
                }
            }

            $snippet .= $coloredLineNumber;
            $snippet .= $this->color->apply(self::LINE_NUMBER_DIVIDER, $this->delimiter);
            $snippet .= $line . PHP_EOL;
        }

        return $snippet;
    }

    protected function coloredLineNumber(string $style, int $lineNum, int $lineStrlen): string
    {
        return $this->color->apply($style, str_pad((string)$lineNum, $lineStrlen, ' ', STR_PAD_LEFT));
    }

}
