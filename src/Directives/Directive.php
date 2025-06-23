<?php

namespace Selene\Directives;

use Selene\Node\DirectiveNode;

abstract class Directive {
    protected array $openingDirectives = [];
    protected array $closingDirectives = [];
    protected array $canRender = [];

    public function canRender(DirectiveNode $directive) : bool {
        return (in_array($directive->getName(), $this->canRender) || 
               in_array($directive->getName(), $this->closingDirectives)) &&
               !in_array($directive->getName(), $this->openingDirectives);
    }

    public function hasOpeningDirective(string $directiveName) : bool {
        return in_array($directiveName, $this->openingDirectives);
    }

    public function hasClosingDirective(string $directiveName) : bool {
        if (empty($this->closingDirectives)) {
            return in_array($directiveName, $this->openingDirectives);
        }

        return in_array($directiveName, $this->closingDirectives);
    }

    public function getOpeningDirectives() : array {
        return $this->openingDirectives;
    }

    public function getExpected(string $directiveName) : string {
        if (str_starts_with($directiveName, 'end')) {
            return str_replace('end', '', $directiveName);
        }

        return 'end' . $directiveName;
    }

    abstract public function render(DirectiveNode $directive) : ?string;
}