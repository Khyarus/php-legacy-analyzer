<?php

namespace PhpLegacyAnalyzer\Rules;

use PhpParser\Node;

interface RuleInterface
{
    /**
     * @return array[] Issues encontradas
     */
    public function apply(Node $node): array;
}
