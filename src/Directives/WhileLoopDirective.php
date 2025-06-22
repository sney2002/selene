<?php

namespace Selene\Directives;

use Selene\Node\DirectiveNode;

class WhileLoopDirective extends Directive {
    protected array $openingDirectives = ['while'];
    protected array $closingDirectives = ['endwhile'];

    public function render(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'while':
                return '<?php while (' . $directive->getParameters() . '): ?>';
            case 'endwhile':
                return '<?php endwhile; ?>';
        }

        return null;
    }
}