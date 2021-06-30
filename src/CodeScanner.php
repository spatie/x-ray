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

    public function __construct($parser = null)
    {
        $this->parser = $parser ?? (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
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
        $rayClasses = $this->findStaticMethodCalls($ast, 'Ray');

        $calls = $this->sortNodesByLineNumber($rayCalls, $rayClasses);

        $this->traverseNodes($file, $results, $calls);

        return $results;
    }

    public function findFunctionCalls(array $ast, string ...$functionNames): array
    {
        $nodeFinder = new NodeFinder();

        $nodes = $nodeFinder->findInstanceOf($ast, FuncCall::class);

        return array_filter($nodes, function(Node $node) use ($functionNames) {
            return in_array($node->name->parts[0], $functionNames, true);
        });
    }

    public function findStaticMethodCalls(array $ast, string ...$classNames): array
    {
        $nodeFinder = new NodeFinder();

        $nodes = $nodeFinder->findInstanceOf($ast, Node\Expr\StaticCall::class);

        return array_filter($nodes, function(Node $node) use ($classNames) {
            return in_array($node->class->parts[0], $classNames, true);
        });
    }

    protected function traverseNodes(File $file, ScanResults $results, array $nodes): void
    {
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new FunctionCallVisitor($file->getRealPath(), $results));

        $traverser->traverse($nodes);
    }

    protected function sortNodesByLineNumber(...$items)
    {
        $result = array_merge(...$items);

        usort($result, function($a, $b) {
            if ($a->name->getAttribute('startLine') > $b->name->getAttribute('startLine')) {
                return 1;
            }

            if ($a->name->getAttribute('startLine') < $b->name->getAttribute('startLine')) {
                return -1;
            }

            return 0;
        });

        return $result;
    }
}
