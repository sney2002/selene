<?php

namespace Selene\Node;

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
} 