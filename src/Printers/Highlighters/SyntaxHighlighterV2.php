<?php

namespace Permafrost\RayScan\Printers\Highlighters;

use Permafrost\PhpCodeSearch\Code\CodeSnippet;

class SyntaxHighlighterV2
{
    public const TOKEN_DEFAULT    = 'token_default';
    public const TOKEN_COMMENT    = 'token_comment';
    public const TOKEN_COMMENT_MULTI_OPEN = 'token_comment_multi_open';
    public const TOKEN_COMMENT_MULTI_CLOSE = 'token_comment_multi_open';
    public const TOKEN_COMMENT_MULTI = 'token_comment_multi';
    public const TOKEN_STRING     = 'token_string';
    public const TOKEN_HTML       = 'token_html';
    public const TOKEN_KEYWORD    = 'token_keyword';
    public const ACTUAL_LINE_MARK = 'actual_line_mark';
    public const LINE_NUMBER      = 'line_number';

    public $lines = [];
    public $lineTokens = [];

    public function highlightSnippet(CodeSnippet $snippet): string
    {
        $code = $snippet->getCode();

        $tokens = $this->tokenize(implode(PHP_EOL, $code), 0);

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

    protected function tokenize($source, $startLine = 0): array
    {
        $tokens = token_get_all('<?'.'php ' . $source);

        $output      = [];
        $currentType = null;
        $previousType = null;
        $buffer      = '';
        $lineNum = $startLine;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                switch ($token[0]) {
                    case T_WHITESPACE:
                        break;

                    case T_OPEN_TAG:
                    case T_OPEN_TAG_WITH_ECHO:
                    case T_CLOSE_TAG:
                    case T_STRING:
                    case T_VARIABLE:
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

                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        if (strpos(trim($token[1]), '/*') === 0 && strpos(trim($token[1]), '*/') === false) {
                            $newType = self::TOKEN_COMMENT_MULTI_OPEN;
                        } else {
                            $newType = self::TOKEN_COMMENT;
                        }
                        break;

                    case T_ENCAPSED_AND_WHITESPACE:
                    case T_CONSTANT_ENCAPSED_STRING:
                        $newType = self::TOKEN_STRING;
                        break;

                    case T_INLINE_HTML:
                        $newType = self::TOKEN_HTML;
                        break;

                    default:
                        if (strpos(trim($token[1]), '*/') !== false && strpos(trim($token[1]), '/*') === false) {
                            $newType = self::TOKEN_COMMENT_MULTI_CLOSE;
                        } else {
                            $newType = self::TOKEN_KEYWORD;
                        }
                }
            } else {
                $newType = $token === '"' ? self::TOKEN_STRING : self::TOKEN_KEYWORD;
            }

            if ($currentType === null) {
                $currentType = $newType;
            }

            if ($currentType !== $newType) {
                $output[]    = [$currentType, $buffer, $lineNum];
                $buffer      = '';
                $currentType = $newType;
            }

            $buffer .= is_array($token) ? $token[1] : $token;
        }

        if (isset($newType)) {
            $output[] = [$newType, $buffer, $lineNum];
        }

        array_shift($output);

        return $output;
    }
}
