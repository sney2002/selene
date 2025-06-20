<?php

namespace Selene\Visitor;

use Selene\Node\CommentNode;
use Selene\Node\ComponentNode;
use Selene\Node\DirectiveNode;
use Selene\Node\InterpolationNode;
use Selene\Node\VerbatimNode;
use Selene\Parser;

class PhpTransformVisitor implements NodeVisitor {

    public function __construct(private array $directives) {}

    public function visitCommentNode(CommentNode $node): mixed {
        return '<?php /* ' . $node->getContent() . ' */ ?>';
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
        foreach ($this->directives as $directive) {
            if ($output = $directive->render($node)) {
                return $output;
            }
        }

        if ($node->getParameters()) {
            return '@' . $node->getName() . '(' . $node->getParameters() . ')';
        }

        return '@' . $node->getName();
    }

    public function visitInterpolationNode(InterpolationNode $node): mixed {
        return '<?php echo e(' . trim($node->getContent()) . '); ?>';
    }

    public function visitVerbatimNode(VerbatimNode $node): mixed {
        return $node->getContent();
    }
}
