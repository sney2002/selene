<?php
namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

class ConditionalsCompiler extends DirectiveCompiler {
    protected array $openingDirectives = ['if', 'unless', 'isset', 'empty'];
    protected array $closingDirectives = ['endif', 'endunless', 'endisset', 'endempty'];
    protected array $canCompile = ['elseif', 'else'];

    public function compile(DirectiveNode $directive) : ?string {
        switch ($directive->getName()) {
            case 'if':
                return '<?php if (' . $directive->getParameters() . '): ?>';
            case 'elseif':
                return '<?php elseif (' . $directive->getParameters() . '): ?>';
            case 'else':
                return '<?php else: ?>';
            case 'unless':
                return '<?php if (! (' . $directive->getParameters() . ')): ?>';
            case 'isset':
                return '<?php if (isset(' . $directive->getParameters() . ')): ?>';
            case 'empty':
                // This must be the @empty of a @forelse loop
                if (! $directive->getParameters()) {
                    return null;
                }

                return '<?php if (empty(' . $directive->getParameters() . ')): ?>';
            case 'endunless':
            case 'endempty':
            case 'endisset':
            case 'endif':
                return '<?php endif; ?>';
        }

        return null;
    }
}