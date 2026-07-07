<?php

namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class ForeachCompiler extends LoopCompiler {
    public function compileForeach(DirectiveNode $node) : string {
        return '<?php foreach (' . $node->getParameters() . '): ?>';
    }

    public function compileEndforeach(DirectiveNode $node) : string {
        return '<?php endforeach; ?>';
    }
}