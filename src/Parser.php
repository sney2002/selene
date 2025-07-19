<?php

namespace Selene;

use Selene\Nodes\Node;
use Selene\Nodes\VerbatimNode;
use Selene\Nodes\InterpolationNode;
use Selene\Nodes\DirectiveNode;
use Selene\Nodes\CommentNode;
use Selene\Nodes\ComponentNode;

class Parser
{
    /**
     * @var string WHITESPACE whitespace character type.
     */
    const WHITESPACE = 'ctype_space';

    /**
     * @var string PRINTABLE printable character type.
     */
    const PRINTABLE = 'ctype_graph';

    /**
     * @var string ALPHA alphabetic character type.
     */
    const ALPHA = 'ctype_alpha';

    /**
     * @var int $index The current position in the template string.
     */
    private int $index = 0;

    /**
     * @var string $template The template string being parsed.
     */
    private string $template;

    /**
     * Initializes the parser with the given template.
     *
     * @param string $template
     */
    public function __construct(string $template)
    {
        $this->template = $template;
    }

    /**
     * Parses the template and returns an array of nodes representing its structure.
     *
     * @return array<Node>
     */
    public function parse(): array
    {
        $nodes = [];

        while (!$this->eof()) {
            $nodes[] = match (true) {
                $this->current(4) === '{{--' => $this->parseComment(),
                $this->current(3) === '<x-' => $this->parseComponent(),
                $this->current(3) === '{!!' => $this->parseInterpolation(escaped: false),
                $this->current(3) === '@{{' => $this->parseEscapedInterpolation(),
                $this->current(2) === '{{' => $this->parseInterpolation(escaped: true),
                $this->current(2) === '@@' => $this->parseEscapedDirective(),
                $this->current() === '@' => $this->parseDirective(),
                default => $this->parseVerbatim(),
            };
        }

        return $nodes;
    }

    /**
     * Parses an interpolation ({{ ... }} or {!! ... !!}).
     *
     * @param bool $escaped
     * @return Node
     */
    private function parseInterpolation(bool $escaped = true): Node
    {
        $content = $this->getInterpolationContent($escaped);

        return new InterpolationNode($content, $escaped);
    }

    /**
     * Parses an escaped interpolation (e.g., @{{ ... }}).
     *
     * @return Node
     */
    private function parseEscapedInterpolation(): Node
    {
        $this->consume('@');

        return new VerbatimNode('{{' . $this->getInterpolationContent() . '}}');
    }

    /**
     * Extracts the content inside an interpolation.
     *
     * @param bool $escaped
     * @return string
     */
    private function getInterpolationContent(bool $escaped = true): string
    {
        $opening = $escaped ? '{{' : '{!!';
        $closing = $escaped ? '}}' : '!!}';
        $closingLength = strlen($closing);

        $this->consume($opening);

        $start = $this->index;

        while (!$this->eof() && $this->current($closingLength) !== $closing) {
            if ($this->current() === '"' || $this->current() === "'") {
                $this->getString();
            }

            $this->consume();
        }

        $content = substr($this->template, $start, $this->index - $start);

        $this->consume($closing);

        return $content;
    }

    /**
     * Parses a directive (e.g., @<name> or @<name>(...)).
     *
     * @return Node
     */
    private function parseDirective(): Node
    {
        [$name, $parameters] = $this->getDirective();

        if (! $name) {
            return new VerbatimNode('@');
        }

        return new DirectiveNode($name, $parameters);
    }

    /**
     * Parses an escaped directive (e.g., @@<name> or @@<name>(...)).
     *
     * @return Node
     */
    private function parseEscapedDirective(): Node
    {
        $this->consume('@');

        [$name, $parameters] = $this->getDirective();

        if (! $name) {
            return new VerbatimNode('@@');
        }

        $rawParameters = $parameters ? '(' . $parameters . ')' : '';

        return new VerbatimNode($name . $rawParameters);
    }

    /**
     * Extracts the directive name and its parameters.
     *
     * @return array
     */
    private function getDirective(): array
    {
        $this->consume('@');

        $name = $this->getContentWhile(self::ALPHA);

        $this->consumeUntilAny(["\n", self::PRINTABLE]);

        $parameters = $this->current() === '(' ? $this->getParenthesesContent() : '';

        return [$name, $parameters];
    }

    /**
     * Parses a comment ({{-- ... --}}).
     *
     * @return Node
     */
    private function parseComment(): Node {
        $this->consume('{{--');

        $comment = $this->getContentUntil('--}}'); 

        $this->consume('--}}');

        return new CommentNode($comment);
    }

    /**
     * Parses a verbatim node (until the next interpolation, directive, component or comment).
     *
     * @return Node
     */
    private function parseVerbatim(): Node
    {
        $content = $this->getContentUntilAny(['{{', '{!!', '<x-', '@']);

        return new VerbatimNode($content);
    }

    /**
     * Extracts a quoted string from the template.
     *
     * @return string
     */
    private function getString(): string
    {
        $quote = $this->current();

        $this->consume($quote);

        $start = $this->index;

        while (!$this->eof() && $this->current() !== $quote) {
            if ($this->current() === '\\') {
                $this->consume();
            }
            $this->consume();
        }

        $string = substr($this->template, $start, $this->index - $start);

        $this->consume($quote);

        return $string;
    }

    /**
     * Extracts the content inside parentheses.
     *
     * @return string
     */
    private function getParenthesesContent(): string
    {
        $this->consume('(');

        $start = $this->index;
        $level = 1;

        while (!$this->eof()) {
            if ($this->current() === '"' || $this->current() === "'") {
                $this->getString();
            }

            if ($this->current() === '(') {
                $level++;
            } else if ($this->current() === ')') {
                $level--;
            }

            if ($level === 0) {
                break;
            }

            $this->consume();
        }

        $content = substr($this->template, $start, $this->index - $start);

        $this->consume(')');

        return trim($content);
    }

    /**
     * Parses a component tag (<x-...></x-...>).
     *
     * @return Node
     */
    private function parseComponent(): Node
    {
        $this->consume('<x-');

        $tagName = trim($this->getContentUntilAny(['>', '/>', self::WHITESPACE]));

        $attributes = $this->getComponentAttributes();

        $this->consumeUntilIncluding('>');

        $isSelfClosing = $this->previous(2) === '/>';

        $content = $isSelfClosing ? '' : $this->getComponentContent($tagName);

        return new ComponentNode($tagName, $attributes, (new self($content))->parse());
    }

    /**
     * Extracts the attributes from a component tag.
     *
     * @return array
     */
    private function getComponentAttributes(): array
    {
        $attributes = [];

        while (!$this->eof()) {
            $this->consumeSpaces();

            if ($this->current() === '>' || $this->current(2) === '/>') {
                break;
            }

            $name = trim($this->getContentUntilAny(['=', '/>', '>', self::WHITESPACE]));

            $this->consumeSpaces();

            $value = $this->current() === '=' ? $this->getComponentAttributeValue() : '';

            $attributes[$name] = $value;
        }

        return $attributes;
    }

    /**
     * Extracts the value of a component attribute.
     *
     * @return string
     */
    private function getComponentAttributeValue(): string
    {
        $this->consume('=');

        $this->consumeSpaces();

        if ($this->current() === '"' || $this->current() === "'") {
            return $this->getString();
        }

        return $this->getContentUntilAny(['>', '/>', self::WHITESPACE]);
    }

    /**
     * Extracts the content inside a component tag.
     *
     * @param string $name
     * @return string
     */
    private function getComponentContent(string $name): string
    {
        $start = $this->index;
        $level = 1;
        
        $closingTagLength = 4 + strlen($name);
        $openingTagLength = 3 + strlen($name);

        while (!$this->eof()) {
            if ($this->current($openingTagLength) === '<x-' . $name) {
                $level++;
            } else if ($this->current($closingTagLength) === '</x-' . $name) {
                $level--;
            }

            if ($level === 0) {
                break;
            }

            $this->consume();
        }

        $content = substr($this->template, $start, $this->index - $start);

        $this->consumeUntilIncluding('>');

        return $content;
    }

    /**
     * Checks if the end of the template has been reached.
     * 
     * @return bool
     */
    private function eof(): bool
    {
        return $this->index >= strlen($this->template);
    }

    /**
     * Returns the current character(s) in the template.
     * 
     * @param int $length
     * @return string
     */
    private function current(int $length = 1): string
    {
        return substr($this->template, $this->index, $length);
    }

    /**
     * Returns the previous character(s) in the template.
     * 
     * @param int $length
     * @return string
     */
    private function previous(int $length = 1): string
    {
        if ($this->index - $length < 0) {
            return '';
        }

        return substr($this->template, $this->index - $length, $length);
    }

    /**
     * Returns the content until any of the given conditions is met.
     * 
     * @param array $conditions
     * @return string
     */
    private function getContentUntilAny(array $conditions): string
    {
        $start = $this->index;
        $this->consumeUntilAny($conditions);
        return substr($this->template, $start, $this->index - $start);
    }

    /**
     * Returns the content until the given condition is met.
     * 
     * @param string|callable $condition
     * @return string
     */
    private function getContentUntil(string|callable $condition): string
    {
        $start = $this->index;
        $this->consumeUntil($condition);
        return substr($this->template, $start, $this->index - $start);
    }

    /**
     * Returns the content while the given condition is true.
     * 
     * @param callable $condition
     * @return string
     */
    private function getContentWhile(callable $condition): string
    {
        $start = $this->index;
        $this->consumeWhile($condition);
        return substr($this->template, $start, $this->index - $start);
    }

    /**
     * Consumes whitespace characters.
     *
     * @return void
     */
    private function consumeSpaces(): void
    {
        while (!$this->eof() && ctype_space($this->current())) {
            $this->consume();
        }
    }

    /**
     * Consumes characters from the template while the given condition is true.
     * 
     * @param callable $condition
     * @return void
     */
    private function consumeWhile(callable $condition): void
    {
        while (!$this->eof() && $condition($this->current())) {
            $this->consume();
        }
    }

    /**
     * Consumes the template until the given token is found, including the token.
     * 
     * @param string $token
     * @return void
     */
    private function consumeUntilIncluding(string $token): void
    {
        $this->consumeUntil($token);
        $this->consume($token);
    }

    /**
     * Consumes the template until the given condition is met.
     * 
     * @param string|callable $condition
     * @return void
     */
    private function consumeUntil(string|callable $condition): void
    {
        if (is_callable($condition)) {
            while (!$this->eof() && ! $condition($this->current())) {
                $this->consume();
            }

            return;
        }

        $tokenLength = strlen($condition);

        while (!$this->eof() && $this->current($tokenLength) !== $condition) {
            $this->consume();
        }
    }

    /**
     * Consumes the template until any of the given conditions is met.
     * 
     * @param array<string|callable> $conditions
     * @return void
     */
    private function consumeUntilAny(array $conditions): void
    {
        while (!$this->eof()) {
            foreach ($conditions as $condition) {
                if (is_callable($condition) && $condition($this->current())) {
                    return;
                }

                if (is_string($condition) && $this->current(strlen($condition)) === $condition) {
                    return;
                }
            }

            $this->consume();
        }
    }

    /**
     * Consumes a single character or a specific token from the template.
     *
     * @param string $token
     * @return void
     */
    private function consume(string $token = ''): void
    {
        if (! $token) {
            $this->index += 1;
            return;
        }
        
        $actualToken = substr($this->template, $this->index, strlen($token));
        assert($actualToken === $token, "Expected token {$token} not found at index {$this->index}, got {$actualToken}");
        
        $this->index += strlen($token);
    }
}