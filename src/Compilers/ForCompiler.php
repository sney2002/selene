<?php

namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class ForCompiler extends LoopCompiler {
    public function compileFor(DirectiveNode $node) : string {
        return '<?php for (' . $node->getParameters() . '): ?>';
    }

    public function compileEndfor(DirectiveNode $node) : string {
        return '<?php endfor; ?>';
    }
}