<?php

namespace Selene\Directives;

use Selene\Node\DirectiveNode;

class ForeachLoopDirective extends Directive {
    protected array $openingDirectives = ['foreach'];
    protected array $closingDirectives = ['endforeach'];

    public function render(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'foreach':
                return '<?php foreach (' . $directive->getParameters() . '): ?>';
            case 'endforeach':
                return '<?php endforeach; ?>';
        }

        return null;
    }
}