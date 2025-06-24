<?php
namespace Selene\Nodes;

use Selene\Visitor\NodeVisitor;

class InterpolationNode implements Node {
    private string $content;
    private bool $escaped;

    public function __construct(string $content, bool $escaped = true) {
        $this->content = $content;
        $this->escaped = $escaped;
    }

    public function isEscaped(): bool {
        return $this->escaped;
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