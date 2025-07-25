<?php

namespace Selene\Visitor;

use Selene\Nodes\CommentNode;
use Selene\Nodes\ComponentNode;
use Selene\Nodes\DirectiveNode;
use Selene\Nodes\InterpolationNode;
use Selene\Nodes\VerbatimNode;
use Selene\Compilers\DirectiveCompiler;
use Selene\Parser;

class PhpTransformVisitor implements NodeVisitor {
    private array $directivesStack = [];
    private array $directiveOpeningStack = [];
    private int $line = 1;

    private array $registeredDirectives = [
        \Selene\Compilers\ConditionalsCompiler::class,
        \Selene\Compilers\ForelseCompiler::class,
        \Selene\Compilers\ForeachCompiler::class,
        \Selene\Compilers\ForCompiler::class,
        \Selene\Compilers\SwitchCompiler::class,
        \Selene\Compilers\WhileCompiler::class,
        \Selene\Compilers\LoopControlCompiler::class,
        \Selene\Compilers\BooleanAttributeCompiler::class,
    ];

    private array $compilers = [];

    public function __construct() {
        foreach ($this->registeredDirectives as $directive) {
            $this->compilers[] = new $directive();
        }
    }

    public function compile(array $nodes) : string {
        $output = '';

        foreach ($nodes as $node) {
            $output .= $node->accept($this);
            $this->line = substr_count($output, "\n") + 1;
        }

        if (count($this->directiveOpeningStack) > 0) {
            [$directive, $line] = end($this->directiveOpeningStack);
            throw new \ParseError('Directive @' . $directive . ' is not closed on line ' . $line);
        }

        return $output;
    }

    public function visitCommentNode(CommentNode $node): mixed {
        return '<?php /*' . $node->getContent() . '*/ ?>';
    }

    public function visitComponentNode(ComponentNode $node): mixed {
        $output = '<x-' . $node->getName() . ' ';

        foreach ($node->getAttributes() as $name => $value) {
            $output .= $name . '="';

            foreach ((new Parser($value))->parse() as $attrValue) {
                $output .= $attrValue->accept($this);
            }

            $output .= '" ';
        }

        $output .= '>';

        foreach ($node->getChildren() as $child) {
            $output .= $child->accept($this);
        }

        $output .= '</x-' . $node->getName() . '>';

        return $output;
    }

    public function visitDirectiveNode(DirectiveNode $node): mixed {
        $compiler = $this->findDirectiveCompilerForNode($node);

        if (! $compiler) {
            return $this->handleUnexpectedDirective($node);
        }

        if (! $compiler->validateContext($node, $this->directivesStack)) {
            $this->handleUnexpectedDirectiveContext($compiler, $node);
        }

        if ($compiler->hasClosingDirective($node->getName())) {
            array_pop($this->directivesStack);
            array_pop($this->directiveOpeningStack);
        }

        return $compiler->compile($node);
    }

    public function visitInterpolationNode(InterpolationNode $node): mixed {
        if ($node->isEscaped()) {
            return '<?php echo e(' . trim($node->getContent()) . '); ?>';
        }

        return '<?php echo ' . trim($node->getContent()) . '; ?>';
    }

    public function visitVerbatimNode(VerbatimNode $node): mixed {
        return $node->getContent();
    }

    private function findDirectiveCompilerForNode(DirectiveNode $node) {
        $currentCompiler = end($this->directivesStack);

        if ($currentCompiler && $currentCompiler->canCompile($node)) {
            return $currentCompiler;
        }

        $compilers = array_filter($this->compilers, function($compiler) use ($node) {
            return $compiler->hasOpeningDirective($node->getName());
        });

        if ($compiler = end($compilers)) {
            $this->directivesStack[] = $compiler;
            $this->directiveOpeningStack[] = [$node->getName(), $this->line];
        }

        return $compiler;
    }

    private function handleUnexpectedDirective(DirectiveNode $node) : string {
        if ($this->canCompileDirective($node)) {
            throw new \ParseError($this->getUnexpectedDirectiveErrorMessage($node));
        }

        if ($node->getParameters()) {
            return '@' . $node->getName() . '(' . $node->getParameters() . ')';
        }

        return '@' . $node->getName();
    }

    private function canCompileDirective(DirectiveNode $node) : bool {
        return !!array_filter($this->compilers, function($compiler) use ($node) {
            return $compiler->canCompile($node);
        });
    }

    private function getUnexpectedDirectiveErrorMessage(DirectiveNode $node) : string {
        if ($directive = end($this->directivesStack)) {
            [$name] = end($this->directiveOpeningStack);

            return strtr('Expected @:expected, got @:got on line :line', [
                ':expected' => $directive->getExpected($name),
                ':got' => $node->getName(),
                ':line' => $this->line,
            ]);
        }

        $directive = $this->getDirectiveCompiler($node);

        return strtr('Expected @:expected, got @:got on line :line', [
            ':expected' => $directive->getOpeningDirectives()[0] ?? $node->getName(),
            ':got' => $node->getName(),
            ':line' => $this->line,
        ]);
    }

    private function handleUnexpectedDirectiveContext(DirectiveCompiler $compiler, DirectiveNode $node) : void {
        $expected = $compiler->getExpected($node->getName());
        throw new \ParseError("Directive @{$node->getName()} outside of {$expected} on line {$this->line}");
    }

    private function getDirectiveCompiler(DirectiveNode $node) : ?DirectiveCompiler {
        $directives = array_filter($this->compilers, function($compiler) use ($node) {
            return $compiler->canCompile($node);
        });

        return end($directives);
    }
}
