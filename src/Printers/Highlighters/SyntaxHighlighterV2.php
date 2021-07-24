<?php

namespace Permafrost\RayScan\Printers\Highlighters;

use Permafrost\PhpCodeSearch\Code\CodeSnippet;

class SyntaxHighlighterV2
{
    public const TOKEN_DEFAULT    = 'token_default';
    public const TOKEN_COMMENT    = 'token_comment';
    public const TOKEN_VARIABLE   = 'token_variable';
    public const TOKEN_STRING     = 'token_string';
    public const TOKEN_HTML       = 'token_html';
    public const TOKEN_KEYWORD    = 'token_keyword';
    public const ACTUAL_LINE_MARK = 'actual_line_mark';
    public const LINE_NUMBER      = 'line_number';

    private const ARROW_SYMBOL        = '>';
    private const DELIMITER           = '>';
    private const ARROW_SYMBOL_UTF8   = '❱';//➜';
    private const DELIMITER_UTF8      = '▕'; // '▶';
    private const LINE_NUMBER_DIVIDER = 'line_divider';
    private const MARKED_LINE_NUMBER  = 'marked_line';
    private const WIDTH               = 3;

    /**
     * Holds the theme.
     *
     * @var array
     */
    private const THEME = [
        self::TOKEN_STRING  => ['color_70'],
        self::TOKEN_VARIABLE => ['color_141'],
        self::TOKEN_COMMENT => ['dark_gray', 'italic'],
        self::TOKEN_KEYWORD => ['color_208'],//['magenta', 'bold'],
        self::TOKEN_DEFAULT => ['default', 'bold'],
        self::TOKEN_HTML    => ['blue', 'bold'],

        self::ACTUAL_LINE_MARK    => ['red', 'bold'],
        self::LINE_NUMBER         => ['dark_gray'],
        self::MARKED_LINE_NUMBER  => ['italic', 'bold'],
        self::LINE_NUMBER_DIVIDER => ['dark_gray'],
    ];
    /** @var ConsoleColor */
    private $color;

    /** @var array */
    private const DEFAULT_THEME = [
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
    /** @var string */
    private $delimiter = self::DELIMITER_UTF8;
    /** @var string */
    private $arrow = self::ARROW_SYMBOL_UTF8;
    /**
     * @var string
     */
    private const NO_MARK = '    ';

    public $lines = [];
    public $lineTokens = [];

    public function highlightSnippet(CodeSnippet $snippet): string
    {
        $code = $snippet->getCode();

        $targetLine = $snippet->getLineNumber();

        return $this->highlight(implode(PHP_EOL, $code), $targetLine, array_keys($code));

        foreach($tokens as $token) {
            $lineNums = [];

            if ($token[0] === self::TOKEN_COMMENT) {
                $lineNums[] = [self::TOKEN_COMMENT, trim($token[1]), $this->findCodeInSnippet($snippet, rtrim($token[1]))];
            }

            if ($token[0] === self::TOKEN_STRING) {
                $lineNums[] = [self::TOKEN_STRING, $token[1], $this->findCodeInSnippet($snippet, $token[1], true)];
            }
        }

        //print_r($this->findCodeInSnippet($snippet, "'hello world'", true));

        foreach($code as $lineNum => &$line) {
            //if (count($lineNums)) {
                foreach($lineNums as $lineNumData) {
                    [$tokenType, $tokenValue, $lineNumbers] = $lineNumData;
                    if (in_array($lineNum, $lineNumbers)) {
                        echo "IN ARRAY $lineNum\n";
                        $line = str_replace($tokenValue, '>>>'.$tokenValue.'<<<', $line);
                    }
                }
            //}

            echo "$line\n";
//            $tokens = $this->tokenize($line, $lineNum);
            //if (is_array($tokens) && count($tokens)) {
//                $lastToken = end($tokens)[0];
//                $lastTokenValue = end($tokens)[1];
                //print_r($tokens);
            //}
        }

        return '';
    }

    protected function findCodeInSnippet(CodeSnippet $snippet, string $code, $matchAnywhere = false): array
    {
        $lineBuffer = [];
        $found = false;

        $lines = explode(PHP_EOL, $code);

        foreach($snippet->getCode() as $lineNum => $line) {
            if (count($lines)) {
                $posResult = strpos(trim($line), trim($lines[0]));

                $found = $matchAnywhere ? ($posResult !== false) : ($posResult === 0);
            }

            if (count($lines) === 0 && count($lineBuffer)) {
                return $lineBuffer;
            }

            if ($found) {
                $lineBuffer[] = $lineNum;
                array_shift($lines);
            }
        }

        return $lineBuffer;


    }



    /**
     * Creates an instance of the Highlighter.
     */
    public function __construct(ConsoleColor $color = null, bool $UTF8 = true)
    {
        $this->color = $color ?: new ConsoleColor();

        foreach (self::DEFAULT_THEME as $name => $styles) {
            if (!$this->color->hasTheme($name)) {
                $this->color->addTheme($name, $styles);
            }
        }

        foreach (self::THEME as $name => $styles) {
            $this->color->addTheme($name, $styles);
        }
        if (!$UTF8) {
            $this->delimiter = self::DELIMITER;
            $this->arrow     = self::ARROW_SYMBOL;
        }
        $this->delimiter .= ' ';
    }

    /**
     * {@inheritdoc}
     */
    public function highlight(string $content, int $line, array $lineNumbers): string
    {
        return ($this->getCodeSnippet($content, $line, 4, 4, $lineNumbers));
    }

    /**
     * @param string $source
     * @param int    $lineNumber
     * @param int    $linesBefore
     * @param int    $linesAfter
     */
    public function getCodeSnippet($source, $lineNumber, $linesBefore = 2, $linesAfter = 2, array $lineNumbers = []): string
    {
        $tempTokenLines = $this->getHighlightedLines($source);

//        $offset     = $lineNumber - $linesBefore - 1;
//        $offset     = max($offset, 0);
//        $length     = $linesAfter + $linesBefore + 1;
//        $tempTokenLines = array_slice($tokenLines, $offset, $length, $preserveKeys = true);
//        $tokenLines = [];

        $index = 0;
        foreach($tempTokenLines as $line) {
            $tokenLines[$lineNumbers[$index]] = $line;
            $index++;
        }

        $lines = $this->colorLines($tokenLines);

        return $this->lineNumbers($lines, $lineNumber);
    }

    /**
     * @param string $source
     */
    private function getHighlightedLines($source): array
    {
        $source = str_replace(["\r\n", "\r"], "\n", $source);
        $tokens = $this->tokenize($source);

        return $this->splitToLines($tokens);
    }

    /**
     * @param string $source
     */
    private function tokenize($source): array
    {
        if (strpos(ltrim($source), '<?'.'php') === false) {
            $source = "<?" ."php {$source}";
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
                        $newType = null;
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

        array_pop($output);

        return $output;
    }

    private function splitToLines(array $tokens): array
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

    private function colorLines(array $tokenLines): array
    {
        $lines = [];
        foreach ($tokenLines as $lineCount => $tokenLine) {
            $line = '';
            foreach ($tokenLine as $token) {
                [$tokenType, $tokenValue] = $token;
                if ($this->color->hasTheme($tokenType)) {
                    $line .= $this->color->apply($tokenType, $tokenValue);
                } else {
                    $line .= $tokenValue;
                }
            }
            $lines[$lineCount] = $line;
        }

        return $lines;
    }

    /**
     * @param int|null $markLine
     */
    private function lineNumbers(array $lines, $markLine = null): string
    {
        $lineStrlen = strlen((string) (array_key_last($lines) + 1));
        $lineStrlen = $lineStrlen < self::WIDTH ? self::WIDTH : $lineStrlen;
        $snippet    = '';
        $mark       = '  ' . $this->arrow . ' ';
        foreach ($lines as $i => $line) {
            $coloredLineNumber = $this->coloredLineNumber(self::LINE_NUMBER, $i, $lineStrlen);

            if ($markLine !== null) {
                $snippet .=
                    ($markLine === $i
                        ? $this->color->apply(self::ACTUAL_LINE_MARK, $mark)
                        : self::NO_MARK
                    );

                $coloredLineNumber =
                    ($markLine === $i ?
                        $this->coloredLineNumber(self::MARKED_LINE_NUMBER, $i, $lineStrlen) :
                        $coloredLineNumber
                    );
            }

            $snippet .= $coloredLineNumber;
            $snippet .= $this->color->apply(self::LINE_NUMBER_DIVIDER, $this->delimiter);
            $snippet .= $line . PHP_EOL;
        }

        return $snippet;
    }

    /**
     * @param string $style
     * @param int    $i
     * @param int    $lineStrlen
     */
    private function coloredLineNumber($style, $i, $lineStrlen): string
    {
        return $this->color->apply($style, str_pad((string) ($i + 1), $lineStrlen, ' ', STR_PAD_LEFT));
    }

}
