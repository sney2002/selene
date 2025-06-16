<?php

namespace Selene\Node;

class DirectiveNode implements Node {
    private string $name;
    private string $parameters;

    public function __construct(string $name, string $parameters = '') {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    public function getType(): NodeType {
        return NodeType::DIRECTIVE;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getParameters(): string {
        return $this->parameters;
    }
} 