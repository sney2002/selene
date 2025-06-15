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
        while ($this->current()) {
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
        $this->consume(2);

        $start = $this->index;

        while ($this->current() && $this->current(2) !== '}}') {
            if ($this->current() === '"' || $this->current() === "'") {
                $this->consumeString();
            }
            $this->consume();
        }

        $content = substr($this->template, $start, $this->index - $start);

        $this->consume(2);

        return [
            'type' => self::INTERPOLATION,
            'content' => $content
        ];
    }

    private function parseDirective(): array
    {
        $this->consume();

        $parameters = '';
        $start = $this->index;

        while ($this->current() && $this->current() !== "\n" && $this->current() !== '(') {
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
        $this->consume(4);

        $start = $this->index;

        $this->consumeUntil('--}}');

        $comment = substr($this->template, $start, $this->index - $start);

        $this->consume(4);

        return [
            'type' => self::COMMENT,
            'content' => $comment
        ];
    }

    private function parseVerbatim(): array
    {
        $start = $this->index;

        $this->consumeUntilAny(['{', '<x-', '@']);

        $content = substr($this->template, $start, $this->index - $start);

        return [
            'type' => self::VERBATIM,
            'content' => $content
        ];
    }

    private function consumeString(): void
    {
        $quote = $this->current();

        $this->consume();
        
        while ($this->current() && $this->current() !== $quote) {
            if ($this->current() === '\\') {
                $this->consume();
            }
            $this->consume();
        }
        $this->consume();
    }

    private function consumeParenthesesContent(): string
    {
        $this->consume();
        $start = $this->index;
        $level = 1;

        while ($this->current() && $level > 0) {
            if ($this->current() === '"' || $this->current() === "'") {
                $this->consumeString();
            }

            if ($this->current() === '(') {
                $level++;
            } else if ($this->current() === ')') {
                $level--;
            }
            $this->consume();
        }

        $content = substr($this->template, $start, $this->index - $start - 1);

        return trim($content);
    }

    private function parseComponent(): array
    {
        $this->consume(3);

        $name = $this->getOpeningTagName();

        $this->consumeUntilIncluding('>');

        $isSelfClosing = $this->previous(2) === '/>';

        $content = $isSelfClosing ? '' : $this->getComponentContent($name);

        return [
            'type' => self::COMPONENT,
            'name' => $name,
            'attributes' => [],
            'children' => (new self($content))->parse()
        ];
    }

    private function getOpeningTagName(): string
    {
        $start = $this->index;

        $this->consumeUntilAny(['>', ' ', '/>']);

        return substr($this->template, $start, $this->index - $start);
    }

    private function getComponentContent(string $name): string
    {
        $start = $this->index;
        $level = 1;
        
        $closingTagLength = 4 + strlen($name);
        $openingTagLength = 3 + strlen($name);

        while ($this->current()) {
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

    /**
     * Consumes the template until the given token is found
     * 
     * @param string $token The token to consume until
     * @return void
     */
    private function consumeUntil(string $token): void
    {
        $tokenLength = strlen($token);

        while ($this->current() && $this->current($tokenLength) !== $token) {
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
        while ($this->current()) {
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
        $this->consume(strlen($token));
    }

    /**
     * Consumes the template by the given length
     * 
     * @param int $length The length to consume
     * @return void
     */
    private function consume(int $length = 1): void
    {
        $this->index += $length;
    }
}