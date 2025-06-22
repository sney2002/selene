<?php

namespace Selene\Directives;

use Selene\Node\DirectiveNode;

class ForelseLoopDirective extends Directive {
    protected array $hasEmptyStack = [];
    protected array $openingDirectives = ['forelse'];
    protected array $closingDirectives = ['endforelse'];
    protected array $canRender = ['empty'];

    public function getExpected(string $directiveName) : string {
        if ($this->hasEmpty()) {
            return 'endforelse';
        }

        return 'empty';
    }

    public function canRender(DirectiveNode $directive) : bool {
        if ($directive->getName() === 'empty') {
            return !$directive->getParameters();
        }

        return parent::canRender($directive);
    }

    public function render(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'forelse':
                $this->foreachStart();
                $iterable = explode(' as ', $directive->getParameters())[0];
                return '<?php if (!empty(' . trim($iterable) . ')): foreach (' . $directive->getParameters() . '): ?>';
            case 'empty':
                $this->emptyFound();
                return '<?php endforeach; else: ?>';
            case 'endforelse':
                $this->foreachEnd();
                return '<?php endif; ?>';
        }

        return null;
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