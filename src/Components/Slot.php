<?php

namespace Selene\Components;

use Stringable;

class Slot implements Stringable {
    private string $content;

    public Attributes $attributes;

    public function __construct(string $content = '', array $attributes = []) {
        $this->content = $content;
        $this->attributes = new Attributes($attributes);
    }

    /**
     * Check if the slot is empty
     * 
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->content === '';
    }

    /**
     * Check if the slot has actual content
     * 
     * @return bool
     */
    public function hasActualContent() : bool
    {
        return preg_replace('/<!--[\s\S]*?-->/', '', $this->content) !== '';
    }

    public function __toString() : string
    {
        return $this->content;
    }
}
