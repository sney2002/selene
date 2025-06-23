<?php

namespace Selene\Visitor;

use Selene\Node\CommentNode;
use Selene\Node\ComponentNode;
use Selene\Node\DirectiveNode;
use Selene\Node\InterpolationNode;
use Selene\Node\VerbatimNode;
use Selene\Directives\Directive;
use Selene\Parser;

class PhpTransformVisitor implements NodeVisitor {
    private array $directivesStack = [];
    private array $directiveOpeningStack = [];
    private int $line = 1;

    private array $registeredDirectives = [
        \Selene\Directives\ConditionalDirectives::class,
        \Selene\Directives\ForelseLoopDirective::class,
        \Selene\Directives\ForeachLoopDirective::class,
        \Selene\Directives\ForLoopDirective::class,
        \Selene\Directives\SwitchDirective::class,
        \Selene\Directives\WhileLoopDirective::class,
        \Selene\Directives\BooleanAttributeDirective::class,
    ];

    private array $directives = [];

    public function __construct() {
        foreach ($this->registeredDirectives as $directive) {
            $this->directives[] = new $directive();
        }
    }

    public function render(array $nodes) : string {
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

        return $directive->render($node);
    }

    public function visitInterpolationNode(InterpolationNode $node): mixed {
        return '<?php echo e(' . trim($node->getContent()) . '); ?>';
    }

    public function visitVerbatimNode(VerbatimNode $node): mixed {
        return $node->getContent();
    }

    private function getDirective(DirectiveNode $node) {
        $directive = end($this->directivesStack);

        if ($directive && $directive->canRender($node)) {
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
        if ($this->canRenderDirective($node)) {
            throw new \ParseError($this->getUnexpectedDirectiveErrorMessage($node));
        }

        if ($node->getParameters()) {
            return '@' . $node->getName() . '(' . $node->getParameters() . ')';
        }

        return '@' . $node->getName();
    }

    private function canRenderDirective(DirectiveNode $node) : bool {
        return !!array_filter($this->directives, function($directive) use ($node) {
            return $directive->canRender($node);
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

    private function getDirectiveRenderer(DirectiveNode $node) : ?Directive {
        $directives = array_filter($this->directives, function($directive) use ($node) {
            return $directive->canRender($node);
        });

        return end($directives);
    }
}
