<?php

namespace Selene\Visitor;

use Selene\Nodes\CommentNode;
use Selene\Nodes\ComponentNode;
use Selene\Nodes\DirectiveNode;
use Selene\Nodes\InterpolationNode;
use Selene\Nodes\VerbatimNode;

interface NodeVisitor {
    public function visitCommentNode(CommentNode $node): mixed;
    public function visitComponentNode(ComponentNode $node): mixed;
    public function visitDirectiveNode(DirectiveNode $node): mixed;
    public function visitInterpolationNode(InterpolationNode $node): mixed;
    public function visitVerbatimNode(VerbatimNode $node): mixed;
}