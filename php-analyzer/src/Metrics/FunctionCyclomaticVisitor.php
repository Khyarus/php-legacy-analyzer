<?php

namespace PhpLegacyAnalyzer\Metrics;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class FunctionCyclomaticVisitor extends NodeVisitorAbstract
{
    public array $functions = [];

    private ?string $currentFunction = null;
    private int $complexity = 1;
    private int $startLine = 0;
    private int $endLine = 0;

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Function_
            || $node instanceof Node\Stmt\ClassMethod
        ) {
            $this->currentFunction = $node->name->toString();
            $this->complexity = 1;
            $this->startLine = $node->getStartLine();
            $this->endLine = $node->getEndLine();
        }

        if ($this->currentFunction) {
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

            if ($node instanceof Node\Expr\BinaryOp\BooleanAnd
             || $node instanceof Node\Expr\BinaryOp\BooleanOr) {
                $this->complexity++;
            }
        }
    }

    public function leaveNode(Node $node)
    {
        if ($this->currentFunction &&
            ($node instanceof Node\Stmt\Function_
             || $node instanceof Node\Stmt\ClassMethod)
        ) {
            $this->functions[] = [
                'name' => $this->currentFunction,
                'complexity' => $this->complexity,
                'startLine' => $this->startLine,
                'endLine' => $this->endLine,
            ];

            $this->currentFunction = null;
        }
    }
}
