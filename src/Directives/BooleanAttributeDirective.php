<?php
namespace Selene\Directives;

use Selene\Node\DirectiveNode;

class BooleanAttributeDirective extends Directive
{
    protected array $openingDirectives = ['checked', 'selected', 'disabled', 'readonly', 'required'];

    public function render(DirectiveNode $node) : string
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