<?php

namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class ForeachCompiler extends DirectiveCompiler {
    protected array $openingDirectives = ['foreach'];
    protected array $closingDirectives = ['endforeach'];

    public function compile(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'foreach':
                return '<?php foreach (' . $directive->getParameters() . '): ?>';
            case 'endforeach':
                return '<?php endforeach; ?>';
        }

        return null;
    }
}