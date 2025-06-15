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

        while ($this->current() && $this->current(4) !== '--}}') {
            $this->consume();
        }

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
        
        while ($this->current() && $this->current() !== '{') {
            $this->consume();
        }

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

        $isSelfClosing = $this->current(2) === '/>';

        while ($this->current() && $this->current() !== '>') {
            $this->consume();
        }

        $this->consume();

        $content = $isSelfClosing ? '' : $this->getComponentContent($name);

        return [
            'type' => self::COMPONENT,
            'name' => $name,
            'children' => (new self($content))->parse()
        ];
    }

    private function getOpeningTagName(): string
    {
        $start = $this->index;

        while ($this->current() && $this->current() !== '>' && $this->current() !== '/>' && $this->current() !== ' ') {
            $this->consume();
        }

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

        while ($this->current() && $this->current() !== '>') {
            $this->consume();
        }

        $this->consume();

        return $content;
    }

    private function current(int $length = 1): string
    {
        return substr($this->template, $this->index, $length);
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