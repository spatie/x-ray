<?php

namespace Spatie\XRay\Support;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class RemoveRayCallRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDefinition('Remove Ray calls', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$x = 'something';
ray($x);
CODE_SAMPLE
            , <<<'CODE_SAMPLE'
$x = 'something';
CODE_SAMPLE
            , ['ray'])]);
    }

    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    public function refactor(Node $node): ?int
    {
        $expr = $node->expr;

        if (! $expr instanceof FuncCall && ! $expr instanceof MethodCall && ! $expr instanceof StaticCall) {
            return null;
        }

        if ($expr instanceof Node\Expr\StaticCall && str_ends_with($expr->class, 'Spatie\\Ray\\Ray')) {
            return NodeTraverser::REMOVE_NODE;
        }

        if ($this->isName($expr->name, 'ray')) {
            return NodeTraverser::REMOVE_NODE;
        }

        if ($this->isName($expr->name, 'rd') && $expr instanceof FuncCall) {
            return NodeTraverser::REMOVE_NODE;
        }

        if ($expr->var->name->parts && in_array('ray', $expr->var->name->parts)) {
            return NodeTraverser::REMOVE_NODE;
        }

        return null;
    }
}
