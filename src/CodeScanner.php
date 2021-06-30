<?php

namespace Permafrost\RayScan;

use Permafrost\RayScan\Results\ScanErrorResult;
use Permafrost\RayScan\Results\ScanResults;
use Permafrost\RayScan\Support\File;
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

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    }

    public function scan(File $file)
    {
        $results = new ScanResults($file);

        try {
            /** @var array|Stmt[] $ast */
            $ast = $this->parser->parse($file->contents());
        } catch (Error $error) {
            $results->addError(new ScanErrorResult($file, $error, "Parse error: {$error->getMessage()}"));

            return $results;
        }

        $rayCalls = $this->findFunctionCalls($ast, 'ray', 'rd');

        $this->traverseNodes($file, $results, $rayCalls);

        return $results;
    }

    protected function findFunctionCalls(array $ast, string ...$functionNames): array
    {
        $nodeFinder = new NodeFinder();

        $nodes = $nodeFinder->findInstanceOf($ast, FuncCall::class);

        return array_filter($nodes, function(Node $node) use ($functionNames) {
            return in_array($node->name->parts[0], $functionNames, true);
        });
    }

    protected function traverseNodes(File $file, ScanResults $results, array $nodes): void
    {
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new FunctionCallVisitor($file->getRealPath(), $results));

        $traverser->traverse($nodes);
    }
}
