<?php

namespace Selene;

class Parser
{
    const VERBATIM = 'verbatim';
    const INTERPOLATION = 'interpolation';

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
            } else {
                $this->parseVerbatim();
            }
        }

        return $this->tokens;
    }

    private function parseInterpolation(): void
    {
        $start = $this->index;

        while ($this->current() && $this->current() !== '}') {
            if ($this->current() === '"' || $this->current() === "'") {
                $this->consumeString();
            }
            $this->consume();
        }

        $this->consume(2);

        $content = substr($this->template, $start, $this->index - $start);

        $this->tokens[] = [
            'type' => self::INTERPOLATION,
            'content' => $content
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
            $this->consume();
        }
        $this->consume();
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
}