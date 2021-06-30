<?php

namespace Permafrost\RayScan\Visitors;

use Permafrost\RayScan\Code\FunctionCallLocation;
use Permafrost\RayScan\Results\ScanResults;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeVisitorAbstract;

class FunctionCallVisitor extends NodeVisitorAbstract
{
    /** @var string $filename */
    protected $filename;

    /** @var ScanResults $results */
    protected $results;

    public function __construct(string $filename, ScanResults $results)
    {
        $this->filename = $filename;
        $this->results = $results;
    }

    public function enterNode(Node $node) {
        if ($node instanceof FuncCall) {
            $location = FunctionCallLocation::create(
                $node->name->parts[0],
                $this->filename,
                $node->getStartLine(),
                $node->getEndLine()
            );

            $this->results->addFromLocation($location);
        }

        if ($node instanceof Node\Expr\StaticCall) {
            $location = FunctionCallLocation::create(
                $node->class->parts[0],
                $this->filename,
                $node->getStartLine(),
                $node->getEndLine()
            );

            $this->results->addFromLocation($location);
        }
    }
}
