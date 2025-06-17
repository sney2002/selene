<?php

namespace Selene;

class Parser
{
    const VERBATIM = 'verbatim';
    const INTERPOLATION = 'interpolation';
    const DIRECTIVE = 'directive';
    const COMMENT = 'comment';
    const COMPONENT = 'component';

    private int $index = 0;
    private string $template;
    private array $tokens = [];

    public function __construct(string $template)
    {
        $this->template = $template;
    }

    public function parse(): array
    {
        while (!$this->eof()) {
            $this->tokens[] = match (true) {
                $this->current(4) === '{{--' => $this->parseComment(),
                $this->current(3) === '<x-' => $this->parseComponent(),
                $this->current(2) === '{{' => $this->parseInterpolation(),
                $this->current() === '@' => $this->parseDirective(),
                default => $this->parseVerbatim(),
            };
        }

        return $this->tokens;
    }

    private function parseInterpolation(): array
    {
        $this->consume('{{');

        $start = $this->index;

        while (!$this->eof() && $this->current(2) !== '}}') {
            if ($this->current() === '"' || $this->current() === "'") {
                $this->getString();
            }
            $this->consume();
        }

        $content = substr($this->template, $start, $this->index - $start);

        $this->consume('}}');

        return [
            'type' => self::INTERPOLATION,
            'content' => $content
        ];
    }

    private function parseDirective(): array
    {
        $this->consume('@');

        $parameters = '';
        $start = $this->index;

        while (!$this->eof() && $this->current() !== "\n" && $this->current() !== '(') {
            $this->consume();
        }

        $name = substr($this->template, $start, $this->index - $start);

        if ($this->current() === '(') {
            $parameters = $this->consumeParenthesesContent();
        }

        return [
            'type' => self::DIRECTIVE,
            'name' => trim($name),
            'parameters' => $parameters
        ];
    }

    private function parseComment(): array {
        $this->consume('{{--');

        $start = $this->index;

        $this->consumeUntil('--}}');

        $comment = substr($this->template, $start, $this->index - $start);

        $this->consume('--}}');

        return [
            'type' => self::COMMENT,
            'content' => $comment
        ];
    }

    private function parseVerbatim(): array
    {
        $start = $this->index;

        $this->consumeUntilAny(['{{', '<x-', '@']);

        $content = substr($this->template, $start, $this->index - $start);

        return [
            'type' => self::VERBATIM,
            'content' => $content
        ];
    }

    private function getString(): string
    {
        $quote = $this->current();

        $this->consume();
        
        $start = $this->index;

        while (!$this->eof() && $this->current() !== $quote) {
            if ($this->current() === '\\') {
                $this->consume();
            }
            $this->consume();
        }

        $string = substr($this->template, $start, $this->index - $start);

        $this->consume();

        return $string;
    }

    private function consumeParenthesesContent(): string
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

        $this->consume(')');

        $content = substr($this->template, $start, $this->index - $start - 1);

        return trim($content);
    }

    private function parseComponent(): array
    {
        $this->consume('<x-');

        $name = $this->getOpeningTagName();

        $attributes = $this->getComponentAttributes();

        $this->consumeUntilIncluding('>');

        $isSelfClosing = $this->previous(2) === '/>';

        $content = $isSelfClosing ? '' : $this->getComponentContent($name);

        return [
            'type' => self::COMPONENT,
            'name' => $name,
            'attributes' => $attributes,
            'children' => (new self($content))->parse()
        ];
    }

    private function getOpeningTagName(): string
    {
        $start = $this->index;

        $this->consumeUntilAny(['>', ' ', '/>', "\n"]);

        return trim(substr($this->template, $start, $this->index - $start));
    }

    private function getComponentAttributes(): array
    {
        $attributes = [];

        while (!$this->eof()) {
            $this->consumeSpaces();
            if ($this->current() === '>' || $this->current(2) === '/>') {
                break;
            }

            $name = $this->getComponentAttributeName();
            $value = '';

            if ($this->current() === '=') {
                $value = $this->getComponentAttributeValue();
            }

            $attributes[$name] = $value;
        }

        return $attributes;
    }

    private function getComponentAttributeName(): string
    {
        $start = $this->index;

        $this->consumeUntilAny(['=', '/>', '>', ' ']);

        $name = trim(substr($this->template, $start, $this->index - $start));

        $this->consumeSpaces();

        return $name;
    }

    private function getComponentAttributeValue(): string
    {
        $this->consume('=');
        $this->consumeSpaces();

        if ($this->current() === '"' || $this->current() === "'") {
            return $this->getString();
        }

        $start = $this->index;

        $this->consumeUntilAny([' ', '>', '/>']);

        return substr($this->template, $start, $this->index - $start);
    }

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
     * Checks if the end of the template has been reached
     * 
     * @return bool
     */
    private function eof(): bool
    {
        return $this->index >= strlen($this->template);
    }

    /**
     * Returns the current character(s) in the template
     * 
     * @param int $length The length of the current character
     * @return string
     */
    private function current(int $length = 1): string
    {
        return substr($this->template, $this->index, $length);
    }

    /**
     * Returns the previous character(s) in the template
     * 
     * @param int $length The length of the previous character
     * @return string
     */
    private function previous(int $length = 1): string
    {
        if ($this->index - $length < 0) {
            return '';
        }

        return substr($this->template, $this->index - $length, $length);
    }

    private function consumeSpaces(): void
    {
        while ($this->current() === ' ' || $this->current() === "\n" || $this->current() === "\t" || $this->current() === "\r") {
            $this->consume();
        }
    }

    /**
     * Consumes the template until the given token is found
     * 
     * @param string $token The token to consume until
     * @return void
     */
    private function consumeUntil(string $token): void
    {
        $tokenLength = strlen($token);

        while (!$this->eof() && $this->current($tokenLength) !== $token) {
            $this->consume();
        }
    }

    /**
     * Consumes the template until any of the given tokens is found
     * 
     * @param array $tokens The tokens to consume until
     * @return void
     */
    private function consumeUntilAny(array $tokens): void
    {
        while (!$this->eof()) {
            foreach ($tokens as $token) {
                if ($this->current(strlen($token)) === $token) {
                    return;
                }
            }

            $this->consume();
        }
    }

    /**
     * Consumes the template until the given token is found, including the token
     * 
     * @param string $token The token to consume until
     * @return void
     */
    private function consumeUntilIncluding(string $token): void
    {
        $this->consumeUntil($token);
        $this->consume($token);
    }

    /**
     * Consumes the template by the given length
     * 
     * @param string $token The token to consume (optional)
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