<?php

namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class SwitchCompiler extends DirectiveCompiler {
    protected array $openingDirectives = ['switch'];
    protected array $closingDirectives = ['endswitch'];
    protected array $canCompile = ['case', 'break', 'default'];

    private array $switchStack = [];

    public function compile(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'switch':
                $this->switchStart();
                return '<?php switch (' . $directive->getParameters() . '):';
            case 'case':
                if ($this->isFirstCase()) { 
                    $this->setFirstCase(false);
                    return 'case (' . $directive->getParameters() . '): ?>';
                }

                return '<?php case (' . $directive->getParameters() . '): ?>';
            case 'break':
                return '<?php break; ?>';
            case 'default':
                return '<?php default: ?>';
            case 'endswitch':
                $this->switchEnd();
                return '<?php endswitch; ?>';
        }

        return null;
    }

    private function switchStart() : void {
        $this->switchStack[] = true;
    }

    private function switchEnd() {
        array_pop($this->switchStack);
    }

    private function isFirstCase() : bool {
        return $this->switchStack[count($this->switchStack) - 1];
    }

    private function setFirstCase(bool $value) : void {
        $this->switchStack[count($this->switchStack) - 1] = $value;
    }
}
