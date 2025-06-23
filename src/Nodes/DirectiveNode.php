<?php
namespace Selene\Nodes;

use Selene\Visitor\NodeVisitor;

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

    public function accept(NodeVisitor $visitor): mixed {
        return $visitor->visitDirectiveNode($this);
    }
} 