<?php

namespace Selene\Directives;

use Selene\Node\DirectiveNode;

class ForLoopDirective implements DirectiveInterface {
    public function render(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'for':
                return '<?php for (' . $directive->getParameters() . '): ?>';
            case 'endfor':
                return '<?php endfor; ?>';
        }

        return null;
    }
}