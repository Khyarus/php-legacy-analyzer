<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PhpParser\ParserFactory;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

use PhpLegacyAnalyzer\Rules\WeakComparisonRule;
use PhpLegacyAnalyzer\Rules\NestedIfRule;
use PhpLegacyAnalyzer\Rules\GodFunctionRule;
use PhpLegacyAnalyzer\Rules\CyclomaticComplexityRule;

/**
 * ===========================
 * Entrada
 * ===========================
 */
$file = $argv[1] ?? null;

if (!$file || !file_exists($file)) {
    echo json_encode([
        'issues' => [],
        'error' => 'Arquivo inválido'
    ]);
    exit(1);
}

$code = file_get_contents($file);

/**
 * ===========================
 * Parser PHP → AST
 * ===========================
 */
$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

try {
    $ast = $parser->parse($code);
} catch (\Throwable $e) {
    echo json_encode([
        'issues' => [],
        'error' => 'Erro ao parsear PHP: ' . $e->getMessage()
    ]);
    exit(1);
}

/**
 * ===========================
 * Rules ativas
 * ===========================
 */
$rules = [
    new WeakComparisonRule(),
    new NestedIfRule(),
    new GodFunctionRule(),
    new CyclomaticComplexityRule(),
];

$issues = [];

/**
 * ===========================
 * Traverser + Rule Engine
 * ===========================
 */
$traverser = new NodeTraverser();

$traverser->addVisitor(
    new class($rules, $issues) extends NodeVisitorAbstract {

        public function __construct(
            private array $rules,
            private array &$issues
        ) {}

        public function enterNode(Node $node)
        {
            foreach ($this->rules as $rule) {
                foreach ($rule->apply($node) as $issue) {
                    $this->issues[] = $issue;
                }
            }
        }
    }
);

$traverser->traverse($ast);

/**
 * ===========================
 * Output FINAL (VS Code)
 * ===========================
 */
echo json_encode([
    'issues' => $issues
], JSON_PRETTY_PRINT);
