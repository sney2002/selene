<?php

namespace Selene;

class Parser
{
    const VERBATIM = 'verbatim';
    const INTERPOLATION = 'interpolation';
    const DIRECTIVE = 'directive';
    const COMMENT = 'comment';

    private int $index = 0;
    private string $template;
    private array $tokens;

    public function __construct(string $template)
    {
        $this->template = $template;
    }

    public function parse(): array
    {
        $this->tokens = [];

        while ($this->index < strlen($this->template)) {
            if ($this->current() === '{' && $this->peek() === '{') {
                $this->parseInterpolation();
            } else if ($this->current() === '@') {
                $this->parseDirective();
            } else {
                $this->parseVerbatim();
            }
        }

        return $this->tokens;
    }

    private function parseInterpolation(): void
    {
        if ($this->peek(4) === '{{--') {
            $this->parseComment();
            return;
        }

        $this->consume(2);

        $start = $this->index;

        while ($this->current() && $this->current() !== '}') {
            if ($this->current() === '"' || $this->current() === "'") {
                $this->consumeString();
            }
            $this->consume();
        }

        $content = substr($this->template, $start, $this->index - $start);

        $this->consume(2);

        $this->tokens[] = [
            'type' => self::INTERPOLATION,
            'content' => $content
        ];
    }

    private function parseDirective(): void
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

        $this->tokens[] = [
            'type' => self::DIRECTIVE,
            'name' => trim($name),
            'parameters' => $parameters
        ];
    }

    private function parseComment(): void {
        $this->consume(4);

        $start = $this->index;

        while ($this->current() && $this->peek(4) !== '--}}') {
            $this->consume();
        }

        $comment = substr($this->template, $start, $this->index - $start);

        $this->consume(4);

        $this->tokens[] = [
            'type' => self::COMMENT,
            'content' => $comment
        ];
    }

    private function parseVerbatim(): void
    {
        $start = $this->index;
        
        while ($this->current() && $this->current() !== '{') {
            $this->consume();
        }

        $content = substr($this->template, $start, $this->index - $start);

        $this->tokens[] = [
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

    private function current(): string
    {
        return $this->template[$this->index] ?? '';
    }

    private function peek(int $length = 1): string
    {
        if ($length > 1) {
            return substr($this->template, $this->index, $this->index + $length);
        }

        return $this->template[$this->index] ?? '';
    }

    private function consume(int $length = 1): void
    {
        $this->index += $length;
    }

    private function consumeIf(string $condition): void
    {
        if ($this->current() === $condition) {
            $this->consume();
        }
    }
}