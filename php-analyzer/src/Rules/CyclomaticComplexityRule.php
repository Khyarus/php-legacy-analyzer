<?php

namespace PhpLegacyAnalyzer\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;

class CyclomaticComplexityRule implements RuleInterface
{
    public function apply(Node $node): array
    {
        if (!$node instanceof Function_) {
            return [];
        }

        $complexity = 1;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($node->getStmts() ?? [])
        );

        foreach ($iterator as $stmt) {
            if ($stmt instanceof Node\Stmt\If_
                || $stmt instanceof Node\Stmt\For_
                || $stmt instanceof Node\Stmt\Foreach_
                || $stmt instanceof Node\Stmt\While_
                || $stmt instanceof Node\Stmt\Case_) {
                $complexity++;
            }
        }

        if ($complexity <= 15) return [];

        return [[
            'rule' => 'Complexidade Ciclomática',
            'severity' => $complexity > 25 ? 'error' : 'warning',
            'message' => "Função \"{$node->name}\" muito complexa ({$complexity})",
            'description' => 'Complexidade ciclomática alta dificulta manutenção e testes.',
            'recommendation' => 'Divida a função em funções menores e use early return.',
            'startLine' => $node->getStartLine(),
            'endLine' => $node->getEndLine()
        ]];
    }
}
