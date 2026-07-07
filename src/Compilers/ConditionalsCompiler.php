<?php
namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class ConditionalsCompiler extends DirectiveCompiler {
    public function compileIf(DirectiveNode $node) : string {
        return '<?php if (' . $node->getParameters() . '): ?>';
    }

    public function compileElseif(DirectiveNode $node) : string {
        return '<?php elseif (' . $node->getParameters() . '): ?>';
    }

    public function compileElse(DirectiveNode $node) : string {
        return '<?php else: ?>';
    }

    public function compileUnless(DirectiveNode $node) : string {
        return '<?php if (! (' . $node->getParameters() . ')): ?>';
    }
    
    public function compileIsset(DirectiveNode $node) : string {
        return '<?php if (isset(' . $node->getParameters() . ')): ?>';
    }

    public function compileEmpty(DirectiveNode $node) : string {
        return '<?php if (empty(' . $node->getParameters() . ')): ?>';
    }

    public function compileEndunless(DirectiveNode $node) : string {
        return '<?php endif; ?>';
    }

    public function compileEndisset(DirectiveNode $node) : string {
        return '<?php endif; ?>';
    }

    public function compileEndempty(DirectiveNode $node) : string {
        return '<?php endif; ?>';
    }

    public function compileEndif(DirectiveNode $node) : string {
        return '<?php endif; ?>';
    }
}