<?php

namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class ForelseCompiler extends LoopCompiler {
    protected array $hasEmptyStack = [];

    public function compileForelse(DirectiveNode $node) : string {
        $this->foreachStart();
        $iterable = explode(' as ', $node->getParameters())[0];
        return '<?php if (!empty(' . trim($iterable) . ')): foreach (' . $node->getParameters() . '): ?>';
    }

    public function compileEmpty(DirectiveNode $node) : string {
        $this->emptyFound();
        return '<?php endforeach; else: ?>';
    }

    public function compileEndforelse(DirectiveNode $node) : string {
        $this->foreachEnd();
        return '<?php endif; ?>';
    }

    public function getExpected(string $directiveName) : string {
        if ($this->hasEmpty()) {
            return 'endforelse';
        }

        return 'empty';
    }

    public function isClosed() : bool {
        return $this->hasEmpty();
    }

    public function canCompile(DirectiveNode $directive) : bool {
        if ($directive->getName() === 'empty') {
            return !$directive->getParameters();
        }

        if ($directive->getName() === 'endforelse') {
            return $this->hasEmpty();
        }

        return parent::canCompile($directive);
    }

    private function foreachStart() : void {
        $this->hasEmptyStack[] = false;
    }

    private function foreachEnd() : void {
        array_pop($this->hasEmptyStack);
    }

    private function emptyFound() : void {
        $this->hasEmptyStack[count($this->hasEmptyStack) - 1] = true;
    }

    private function hasEmpty() : bool {
        return $this->hasEmptyStack[count($this->hasEmptyStack) - 1] ?? false;
    }
}