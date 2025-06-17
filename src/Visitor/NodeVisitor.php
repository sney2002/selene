<?php

namespace Selene\Visitor;

use Selene\Node\CommentNode;
use Selene\Node\ComponentNode;
use Selene\Node\DirectiveNode;
use Selene\Node\InterpolationNode;
use Selene\Node\VerbatimNode;

interface NodeVisitor {
    public function visitCommentNode(CommentNode $node): mixed;
    public function visitComponentNode(ComponentNode $node): mixed;
    public function visitDirectiveNode(DirectiveNode $node): mixed;
    public function visitInterpolationNode(InterpolationNode $node): mixed;
    public function visitVerbatimNode(VerbatimNode $node): mixed;
}