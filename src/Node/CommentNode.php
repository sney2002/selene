<?php

namespace Selene\Node;

use Selene\Visitor\NodeVisitor;

class CommentNode implements Node {
    private string $content;

    public function __construct(string $content) {
        $this->content = $content;
    }

    public function getType(): NodeType {
        return NodeType::COMMENT;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function accept(NodeVisitor $visitor): mixed {
        return $visitor->visitCommentNode($this);
    }
} 