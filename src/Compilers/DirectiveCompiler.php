<?php

namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

abstract class DirectiveCompiler {
    private array $openingDirectives = [];
    private array $closingDirectives = [];

    public function __construct() {
        $this->closingDirectives = $this->getDirectives(function($directiveName) {
            return str_starts_with($directiveName, 'end');
        });

        $this->openingDirectives = $this->getDirectives(function($directiveName) {
            if (empty($this->closingDirectives)) {
                return true;
            }

            return in_array('end' . $directiveName, $this->closingDirectives);
        });
    }

    public function canCompile(DirectiveNode $directive) : bool {
        return $this->hasOptionalDirective($directive->getName()) || 
               $this->hasClosingDirective($directive->getName());
    }

    public function validateContext(DirectiveNode $directive, array $directiveStack) : bool {
        return true;
    }

    public function hasOpeningDirective(string $directiveName) : bool {
        return in_array($directiveName, $this->openingDirectives);
    }

    public function hasClosingDirective(string $directiveName) : bool {
        // "Self closing" directives like @continue @style...
        if (empty($this->closingDirectives)) {
            return $this->hasOpeningDirective($directiveName);
        }

        return in_array($directiveName, $this->closingDirectives);
    }

    private function hasOptionalDirective(string $directiveName) : bool {
        return method_exists($this, 'compile' . $directiveName) && 
               !method_exists($this, 'compileend' . $directiveName);
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

    public function compile(DirectiveNode $directive) : string {
        return $this->{'compile' . $directive->getName()}($directive);
    }

    private function getDirectives(\Closure $callback) : array {
        $methods = array_filter(get_class_methods($this::class), function($methodName) {
            return str_starts_with($methodName, 'compile') && $methodName !== 'compile';
        });

        $directives = array_map(function($method) {
            return strtolower(str_replace('compile', '', $method));
        }, $methods);

        return array_filter($directives, $callback);
    }
}