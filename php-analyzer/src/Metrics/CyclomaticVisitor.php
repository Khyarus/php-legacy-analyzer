<?php

namespace PhpLegacyAnalyzer\Metrics;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class CyclomaticVisitor extends NodeVisitorAbstract
{
    public int $complexity = 1;

    public function enterNode(Node $node)
    {
        if (
            $node instanceof Node\Stmt\If_
            || $node instanceof Node\Stmt\ElseIf_
            || $node instanceof Node\Stmt\For_
            || $node instanceof Node\Stmt\Foreach_
            || $node instanceof Node\Stmt\While_
            || $node instanceof Node\Stmt\Do_
            || $node instanceof Node\Stmt\Case_
            || $node instanceof Node\Stmt\Catch_
        ) {
            $this->complexity++;
        }

        // operadores lógicos
        if ($node instanceof Node\Expr\BinaryOp\BooleanAnd
         || $node instanceof Node\Expr\BinaryOp\BooleanOr) {
            $this->complexity++;
        }
    }
}
