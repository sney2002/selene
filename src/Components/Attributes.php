<?php

namespace Selene\Components;

use Selene\Support\Attr;
use Stringable;

class Attributes implements Stringable {
    public function __construct(
        private array $attributes = [],
    ) {}

    /**
     * Get all attributes
     * 
     * @return array
     */
    public function all() : array
    {
        return $this->attributes;
    }

    /**
     * Get a single attribute
     * 
     * @param string $name
     * @return string
     */
    public function get(string $name) : mixed
    {
        return $this->attributes[$name] ?? '';
    }

    /**
     * Check if an attribute exists
     * 
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Conditionally merge classes
     * 
     * @param array $classes
     * @return self
     */
    public function class(array $classes) : self
    {
        return $this->merge(['class' => Attr::class($classes)]);
    }

    /**
     * Conditionally merge styles
     * 
     * @param array $styles
     * @return self
     */
    public function style(array $styles) : self
    {
        return $this->merge(['style' => Attr::style($styles)]);
    }

    /**
     * Merge attributes
     * 
     * @param array $attributes
     * @return self
     */
    public function merge(array $attributes) : self
    {
        foreach ($attributes as $key => $value) {
            if ($key === 'class' || $key === 'style') {
                $this->attributes[$key] = $this->appendToAttribute($key, $value);
            } else if (! isset($this->attributes[$key])) {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Remove the specified attributes
     * 
     * @param array $attributes
     * @return self
     */
    public function except(array $attributes) : self
    {
        return new self(array_diff_key($this->attributes, array_flip($attributes)));
    }

    /**
     * Keep the specified attributes
     * 
     * @param array $attributes
     * @return self
     */
    public function only(array $attributes) : self
    {
        return new self(array_intersect_key($this->attributes, array_flip($attributes)));
    }

    /**
     * Convert the attributes to a string
     * 
     * @return string
     */
    public function __toString() : string
    {
        $string = '';

        foreach ($this->attributes as $key => $value) {
            if ($value === false) {
                continue;
            }

            if (is_bool($value)) {
                $string .= ' ' . $key;
            } else {
                $string .= ' ' . $key . '="' . str_replace('"', '\\"', e($value)) . '"';
            }
        }

        return trim($string);
    }

    /**
     * Append to attribute
     * 
     * @param string $attribute
     * @param string $value
     * @return string
     */
    private function appendToAttribute(string $attribute, string $value) : string
    {
        $delimiter = $attribute === 'style' ? ';' : ' ';

        $values = array_merge(
            explode($delimiter, $this->get($attribute)),
            explode($delimiter, $value),
        );

        $values = array_unique(
            array_filter(array_map('trim', $values))
        );

        if ($attribute === 'style') {
            return implode('; ', $values) . ';';
        }

        return implode(' ', $values);
    }
}