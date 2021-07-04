<?php

namespace Permafrost\RayScan;

use Permafrost\RayScan\Configuration\Configuration;
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

    /** @var Configuration */
    protected $config;

    /** @var array */
    protected $ast = [];

    public function __construct(Configuration $config, $parser = null)
    {
        $this->config = $config;
        $this->parser = $parser ?? (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
    }

    public function scan(File $file): ScanResults
    {
        $results = new ScanResults($file);

        if (! $this->parseFile($file, $results)) {
            return $results;
        }

        $calls = $this->findAllCalls($this->ast);

        $this->traverseNodes($file, $results, $calls);

        return $results;
    }

    protected function parseFile(File $file, ScanResults $results): bool
    {
        try {
            /** @var array|Stmt[] $ast */
            $this->ast = $this->parser->parse($file->contents());
        } catch (Error $error) {
            $results->addError(new ScanErrorResult($file, $error, "Parse error: {$error->getMessage()}"));

            return false;
        }

        return true;
    }

    protected function findAllCalls(array $ast): array
    {
        $functionNames = array_diff(['ray', 'rd'], $this->config->ignoreFunctions);

        $rayCalls = $this->findFunctionCalls($ast, ...$functionNames);
        $rayClasses = $this->findStaticMethodCalls($ast, 'Ray');

        return $this->sortNodesByLineNumber($rayCalls, $rayClasses);
    }

    protected function findCalls(array $ast, string $class, string $nodeNameProp, array $names): array
    {
        $nodeFinder = new NodeFinder();

        $nodes = $nodeFinder->findInstanceOf($ast, $class);

        return array_filter($nodes, function(Node $node) use ($names, $nodeNameProp) {
            if (! isset($node->name->parts)) {
                return false;
            }

            return in_array($node->{$nodeNameProp}->parts[0], $names, true);
        });
    }

    public function findFunctionCalls(array $ast, string ...$functionNames): array
    {
        return $this->findCalls($ast, FuncCall::class, 'name', $functionNames);
    }

    public function findStaticMethodCalls(array $ast, string ...$classNames): array
    {
        return $this->findCalls($ast, Node\Expr\StaticCall::class, 'class', $classNames);
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
