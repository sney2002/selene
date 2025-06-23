<?php
namespace Selene\Nodes;

use Selene\Visitor\NodeVisitor;

class VerbatimNode implements Node {
    private string $content;

    public function __construct(string $content) {
        $this->content = $content;
    }

    public function getType(): NodeType {
        return NodeType::VERBATIM;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function accept(NodeVisitor $visitor): mixed {
        return $visitor->visitVerbatimNode($this);
    }
} 