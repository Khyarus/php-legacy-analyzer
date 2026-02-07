<?php

namespace PhpLegacyAnalyzer\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;

class NestedIfRule implements RuleInterface
{
    public function apply(Node $node): array
    {
        if (!$node instanceof If_) return [];

        $depth = 0;
        $current = $node;

        while ($current instanceof If_) {
            $depth++;
            $current = $current->stmts[0] ?? null;
        }

        if ($depth <= 3) return [];

        return [[
            'rule' => 'If Aninhado',
            'severity' => $depth > 4 ? 'error' : 'warning',
            'message' => "If aninhado demais ({$depth} níveis)",
            'description' => 'Ifs muito aninhados reduzem legibilidade.',
            'recommendation' => 'Use guard clauses e early return.',
            'startLine' => $node->getStartLine(),
            'endLine' => $node->getEndLine()
        ]];
    }
}
