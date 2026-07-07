<?php

namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class WhileCompiler extends LoopCompiler {
    public function compileWhile(DirectiveNode $node) : string {
        return '<?php while (' . $node->getParameters() . '): ?>';
    }

    public function compileEndwhile(DirectiveNode $node) : string {
        return '<?php endwhile; ?>';
    }
}