<?php

namespace Selene\Directives;

use Selene\Node\DirectiveNode;

class ConditionalDirectives implements DirectiveInterface {
    public function render(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'if':
                return '<?php if (' . $directive->getParameters() . '): ?>';
            case 'elseif':
                return '<?php elseif (' . $directive->getParameters() . '): ?>';
            case 'else':
                return '<?php else: ?>';
            case 'unless':
                return '<?php if (! (' . $directive->getParameters() . ')): ?>';
            case 'isset':
                return '<?php if (isset(' . $directive->getParameters() . ')): ?>';
            case 'empty':
                // This must be the @empty of a @forelse loop
                if (! $directive->getParameters()) {
                    return null;
                }

                return '<?php if (empty(' . $directive->getParameters() . ')): ?>';
            case 'endunless':
            case 'endempty':
            case 'endisset':
            case 'endif':
                return '<?php endif; ?>';
        }

        return null;
    }
}