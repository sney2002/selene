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
        \Selene\Compilers\BooleanAttributeCompiler::class,
    ];

    private array $directives = [];

    public function __construct() {
        foreach ($this->registeredDirectives as $directive) {
            $this->directives[] = new $directive();
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
        $directive = $this->getDirective($node);

        if (! $directive) {
            return $this->handleUnexpectedDirective($node);
        }

        if ($directive->hasClosingDirective($node->getName())) {
            array_pop($this->directivesStack);
            array_pop($this->directiveOpeningStack);
        }

        return $directive->compile($node);
    }

    public function visitInterpolationNode(InterpolationNode $node): mixed {
        return '<?php echo e(' . trim($node->getContent()) . '); ?>';
    }

    public function visitVerbatimNode(VerbatimNode $node): mixed {
        return $node->getContent();
    }

    private function getDirective(DirectiveNode $node) {
        $directive = end($this->directivesStack);

        if ($directive && $directive->canCompile($node)) {
            return $directive;
        }

        $directives = array_filter($this->directives, function($directive) use ($node) {
            return $directive->hasOpeningDirective($node->getName());
        });

        if ($directive = end($directives)) {
            $this->directivesStack[] = $directive;
            $this->directiveOpeningStack[] = [$node->getName(), $this->line];
        }

        return $directive;
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
        return !!array_filter($this->directives, function($directive) use ($node) {
            return $directive->canCompile($node);
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

        $directive = $this->getDirectiveRenderer($node);

        return strtr('Expected @:expected, got @:got on line :line', [
            ':expected' => $directive->getOpeningDirectives()[0] ?? $node->getName(),
            ':got' => $node->getName(),
            ':line' => $this->line,
        ]);
    }

    private function getDirectiveRenderer(DirectiveNode $node) : ?DirectiveCompiler {
        $directives = array_filter($this->directives, function($directive) use ($node) {
            return $directive->canCompile($node);
        });

        return end($directives);
    }
}
