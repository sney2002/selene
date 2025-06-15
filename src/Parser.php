<?php

namespace Selene;

class Parser
{
    const VERBATIM = 'verbatim';
    const INTERPOLATION = 'interpolation';
    const DIRECTIVE = 'directive';

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
            if ($this->current() === '{' && $this->peak() === '{') {
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

    private function peak(int $offset = 1): string
    {
        return $this->template[$this->index + $offset] ?? '';
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