<?php

namespace Permafrost\RayScan;

use Permafrost\RayScan\Results\ScanResults;
use Permafrost\RayScan\Visitors\FunctionCallVisitor;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

class CodeScanner
{
    /** @var ParserFactory $parser */
    protected $parser;

    /** @var array|Stmt[] */
    protected $ast;

    /** @var string $filename */
    public $filename;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    }

    public function scan(string $filename, string $code)
    {
        $this->filename = realpath($filename);

        try {
            $this->ast = $this->parser->parse($code);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";

            return false;
        }

        $results = new ScanResults();

        $rayCalls = $this->findFunctionCalls('ray', 'rd');

        $this->traverseNodes($results, $rayCalls);

        return $results;
    }

    protected function findFunctionCalls(string ...$functionNames): array
    {
        $nodeFinder = new NodeFinder();

        $nodes = $nodeFinder->findInstanceOf($this->ast, FuncCall::class);

        return array_filter($nodes, function(Node $node) use ($functionNames) {
            return in_array($node->name->parts[0], $functionNames, true);
        });
    }

    protected function traverseNodes(ScanResults $results, array $nodes)
    {
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new FunctionCallVisitor($this->filename, $results));

        $traverser->traverse($nodes);
    }
}
