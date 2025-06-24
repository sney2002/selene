<?php
namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class LoopControlCompiler extends DirectiveCompiler {
    protected array $openingDirectives = ['continue', 'break'];

    public function compile(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'continue':
                if ($directive->getParameters()) {
                    return '<?php if (' . $directive->getParameters() . '): continue; endif; ?>';
                }

                return '<?php continue; ?>';
            case 'break':
                if ($directive->getParameters()) {
                    return '<?php if (' . $directive->getParameters() . '): break; endif; ?>';
                }
                return '<?php break; ?>';
            default:
                return null;
        }
    }
}