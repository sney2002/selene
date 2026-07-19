<?php

namespace Selene\Support;

class Str {
    public static function kebab(string $string) : string {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $string));
    }
}
