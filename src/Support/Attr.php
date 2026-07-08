<?php

namespace Selene\Support;

class Attr {
    /**
     * Convert styles to a string
     * 
     * @param array $styles
     * @return string
     */
    public static function style(array $styles) : string
    {
        $newStyles = [];

        foreach ($styles as $style => $condition) {
            if (is_numeric($style)) {
                $newStyles[] = $condition;
            } else if ($condition) {
                $newStyles[] = $style;
            }
        }

        return implode('; ', $newStyles);
    }

    public static function class(array $classes) : string
    {
        $newClasses = [];

        foreach ($classes as $class => $condition) {
            if (is_numeric($class)) {
                $newClasses[] = $condition;
            } else if ($condition) {
                $newClasses[] = $class;
            }
        }

        return implode(' ', $newClasses);
    }
}