<?php

namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class ForCompiler extends DirectiveCompiler {
    protected array $openingDirectives = ['for'];
    protected array $closingDirectives = ['endfor'];

    public function compile(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'for':
                return '<?php for (' . $directive->getParameters() . '): ?>';
            case 'endfor':
                return '<?php endfor; ?>';
        }

        return null;
    }
}