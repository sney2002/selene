<?php

namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class SwitchCompiler extends DirectiveCompiler {
    private array $switchStack = [];

    public function compileSwitch(DirectiveNode $node) : string {
        $this->switchStart();
        return '<?php switch (' . $node->getParameters() . '):';
    }

    public function compileCase(DirectiveNode $node) : string {
        if ($this->isFirstCase()) { 
            $this->setFirstCase(false);
            return 'case (' . $node->getParameters() . '): ?>';
        }

        return '<?php case (' . $node->getParameters() . '): ?>';
    }

    public function compileBreak(DirectiveNode $node) : string {
        return '<?php break; ?>';
    }

    public function compileDefault(DirectiveNode $node) : string {
        return '<?php default: ?>';
    }

    public function compileEndswitch(DirectiveNode $node) : string {
        $this->switchEnd();
        return '<?php endswitch; ?>';
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
