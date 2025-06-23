<?php
namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class BooleanAttributeCompiler extends DirectiveCompiler
{
    protected array $openingDirectives = ['checked', 'selected', 'disabled', 'readonly', 'required'];

    public function compile(DirectiveNode $node) : string
    {
        switch ($node->getName()) {
            case 'checked':
                return "<?php if ({$node->getParameters()}): echo 'checked'; endif; ?>";
            case 'selected':
                return "<?php if ({$node->getParameters()}): echo 'selected'; endif; ?>";
            case 'disabled':
                return "<?php if ({$node->getParameters()}): echo 'disabled'; endif; ?>";
            case 'readonly':
                return "<?php if ({$node->getParameters()}): echo 'readonly'; endif; ?>";
            case 'required':
                return "<?php if ({$node->getParameters()}): echo 'required'; endif; ?>";
            default:
                return '';
        }
    }
}