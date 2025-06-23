<?php
namespace Selene\Nodes;

use Selene\Visitor\NodeVisitor;

class ComponentNode implements Node {
    private string $name;
    private array $attributes;
    private array $children;

    public function __construct(string $name, array $attributes = [], array $children = []) {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->children = $children;
    }

    public function getType(): NodeType {
        return NodeType::COMPONENT;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function getChildren(): array {
        return $this->children;
    }

    public function accept(NodeVisitor $visitor): mixed {
        return $visitor->visitComponentNode($this);
    }
} 