<?php

namespace Selene\Node;

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
} 