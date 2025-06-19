<?php

namespace Selene\Directives;

use Selene\Node\DirectiveNode;

class ForelseLoopDirective implements DirectiveInterface {
    public function render(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'forelse':
                $iterable = explode(' as ', $directive->getParameters())[0];
                return '<?php if (!empty(' . trim($iterable) . ')): foreach (' . $directive->getParameters() . '): ?>';
            case 'empty':
                return '<?php endforeach; else: ?>';
            case 'endforelse':
                return '<?php endif; ?>';
        }

        return null;
    }
}