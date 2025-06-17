<?php

namespace Selene\Node;

use Selene\Visitor\NodeVisitor;

class InterpolationNode implements Node {
    private string $content;

    public function __construct(string $content) {
        $this->content = $content;
    }

    public function getType(): NodeType {
        return NodeType::INTERPOLATION;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function accept(NodeVisitor $visitor): mixed {
        return $visitor->visitInterpolationNode($this);
    }
} 