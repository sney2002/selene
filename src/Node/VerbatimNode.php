<?php

namespace Selene\Node;

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
} 