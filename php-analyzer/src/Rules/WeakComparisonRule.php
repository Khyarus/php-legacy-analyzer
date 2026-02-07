<?php

namespace PhpLegacyAnalyzer\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Equal;

class WeakComparisonRule implements RuleInterface
{
    public function apply(Node $node): array
    {
        if (!$node instanceof Equal) return [];

        return [[
            'rule' => 'Comparação Fraca',
            'severity' => 'warning',
            'message' => 'Uso de comparação fraca (==)',
            'description' => 'Comparações fracas causam bugs em PHP antigo.',
            'recommendation' => 'Use === para evitar type juggling.',
            'startLine' => $node->getStartLine(),
            'endLine' => $node->getEndLine()
        ]];
    }
}
