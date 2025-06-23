<?php

namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class WhileCompiler extends DirectiveCompiler {
    protected array $openingDirectives = ['while'];
    protected array $closingDirectives = ['endwhile'];

    public function compile(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'while':
                return '<?php while (' . $directive->getParameters() . '): ?>';
            case 'endwhile':
                return '<?php endwhile; ?>';
        }

        return null;
    }
}