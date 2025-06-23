<?php
namespace Selene\Nodes;

use Selene\Visitor\NodeVisitor;

interface Node {
    public function getType(): NodeType;
    public function accept(NodeVisitor $visitor): mixed;
}