<?php
namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class BooleanAttributeCompiler extends DirectiveCompiler
{
    public function compileChecked(DirectiveNode $node) : string
    {
        return "<?php if ({$node->getParameters()}): echo 'checked'; endif; ?>";
    }

    public function compileSelected(DirectiveNode $node) : string
    {
        return "<?php if ({$node->getParameters()}): echo 'selected'; endif; ?>";
    }
    
    public function compileDisabled(DirectiveNode $node) : string
    {
        return "<?php if ({$node->getParameters()}): echo 'disabled'; endif; ?>";
    }

    public function compileReadonly(DirectiveNode $node) : string
    {
        return "<?php if ({$node->getParameters()}): echo 'readonly'; endif; ?>";
    }
    
    
    public function compileRequired(DirectiveNode $node) : string
    {
        return "<?php if ({$node->getParameters()}): echo 'required'; endif; ?>";
    }
}