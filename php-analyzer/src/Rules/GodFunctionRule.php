<?php

namespace PhpLegacyAnalyzer\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;

class GodFunctionRule implements RuleInterface
{
    public function apply(Node $node): array
    {
        if (!$node instanceof Function_) return [];

        $lines = $node->getEndLine() - $node->getStartLine();

        if ($lines < 50) return [];

        return [[
            'rule' => 'God Function',
            'severity' => $lines > 80 ? 'error' : 'warning',
            'message' => "Função \"{$node->name}\" muito longa ({$lines} linhas)",
            'description' => 'Funções longas indicam múltiplas responsabilidades.',
            'recommendation' => 'Extraia métodos menores com responsabilidades únicas.',
            'startLine' => $node->getStartLine(),
            'endLine' => $node->getEndLine()
        ]];
    }
}
