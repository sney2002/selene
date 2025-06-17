<?php

namespace Selene\Node;

use Selene\Visitor\NodeVisitor;

interface Node {
    public function getType(): NodeType;
    public function accept(NodeVisitor $visitor): mixed;
} 