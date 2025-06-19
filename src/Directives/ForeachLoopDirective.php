<?php

namespace Selene\Directives;

use Selene\Node\DirectiveNode;

class ForeachLoopDirective implements DirectiveInterface {
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