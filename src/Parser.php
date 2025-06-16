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

        $start = $this->index;

        while (!$this->eof() && ctype_alpha($this->current())) {
            $this->consume();
        }

        $name = substr($this->template, $start, $this->index - $start);

        while (!$this->eof()) {
            if (! ctype_space($this->current())) {
                break;
            }

            if ($this->current() === "\n") {
                break;
            }

            $this->consume();
        }

        $parameters = $this->current() === '(' ? $this->consumeParenthesesContent() : '';

        return [
            'type' => self::DIRECTIVE,
            'name' => trim($name),
            'parameters' => $parameters
        ];
    }

    private function parseComment(): array {
        $this->consume('{{--');

        $comment = $this->getContentUntil('--}}'); 

        $this->consume('--}}');

        return [
            'type' => self::COMMENT,
            'content' => $comment
        ];
    }

    private function parseVerbatim(): array
    {
        $content = $this->getContentUntilAny(['{{', '<x-', '@']);

        return [
            'type' => self::VERBATIM,
            'content' => $content
        ];
    }

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

        $content = substr($this->template, $start, $this->index - $start);

        $this->consume(')');

        return trim($content);
    }

    private function parseComponent(): array
    {
        $this->consume('<x-');

        $tagName = trim($this->getContentUntilAny(['>', ' ', '/>', "\n", "\t", "\r"]));

        $attributes = $this->getComponentAttributes();

        $this->consumeUntilIncluding('>');

        $isSelfClosing = $this->previous(2) === '/>';

        $content = $isSelfClosing ? '' : $this->getComponentContent($tagName);

        return [
            'type' => self::COMPONENT,
            'name' => $tagName,
            'attributes' => $attributes,
            'children' => (new self($content))->parse()
        ];
    }

    private function getComponentAttributes(): array
    {
        $attributes = [];

        while (!$this->eof()) {
            $this->consumeSpaces();

            if ($this->current() === '>' || $this->current(2) === '/>') {
                break;
            }

            $name = trim($this->getContentUntilAny(['=', '/>', '>', ' ', "\n", "\t", "\r"]));

            $this->consumeSpaces();

            $value = $this->current() === '=' ? $this->getComponentAttributeValue() : '';

            $attributes[$name] = $value;
        }

        return $attributes;
    }


    private function getComponentAttributeValue(): string
    {
        $this->consume('=');

        $this->consumeSpaces();

        if ($this->current() === '"' || $this->current() === "'") {
            return $this->getString();
        }

        return $this->getContentUntilAny([' ', '>', '/>']);
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
        $whitespaceChars = [' ', "\n", "\t", "\r"];

        while (!$this->eof() && in_array($this->current(), $whitespaceChars)) {
            $this->consume();
        }
    }

    /**
     * Returns the content until any of the given tokens is found
     * 
     * @param array $tokens The tokens to consume until
     * @return string
     */
    private function getContentUntilAny(array $tokens): string
    {
        $start = $this->index;
        $this->consumeUntilAny($tokens);
        return substr($this->template, $start, $this->index - $start);
    }

    /**
     * Returns the content until the given token is found
     * 
     * @param string $token The token to consume until
     * @return string
     */
    private function getContentUntil(string $token): string
    {
        $start = $this->index;
        $this->consumeUntil($token);
        return substr($this->template, $start, $this->index - $start);
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
     * Consumes the template until any of the given conditions is met
     * 
     * @param array<string|callable> $conditions The conditions to consume until
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