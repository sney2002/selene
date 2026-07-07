<?php
namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class LoopControlCompiler extends DirectiveCompiler {
    public function compileContinue(DirectiveNode $directive) : string {
        if ($directive->getParameters()) {
            return '<?php if (' . $directive->getParameters() . '): continue; endif; ?>';
        }

        return '<?php continue; ?>';
    }

    public function compileBreak(DirectiveNode $directive) : string {
        if ($directive->getParameters()) {
            return '<?php if (' . $directive->getParameters() . '): break; endif; ?>';
        }

        return '<?php break; ?>';
    }

    public function getExpected(string $directiveName) : string {
        return 'loop';
    }

    public function validateContext(DirectiveNode $directive, array $directiveStack) : bool {
        foreach ($directiveStack as $compiler) {
            if ($compiler instanceof LoopCompiler && ! $compiler->isClosed()) {
                return true;
            }
        }

        return false;
    }
}