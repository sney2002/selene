<?php

namespace Tests\Unit\Components;

use Selene\Components\Attributes;

test('Can get all attributes', function() {
    $attributes = new Attributes(['id' => 'hello']);
    expect($attributes->all())->toBe(['id' => 'hello']);
});

test('Can check if an attribute exists', function() {
    $attributes = new Attributes(['id' => 'hello']);
    expect($attributes->has('id'))->toBeTrue();
    expect($attributes->has('class'))->toBeFalse();
});

describe('get', function() {
    test('Can get a single attribute', function() {
        $attributes = new Attributes(['id' => 'hello']);
        expect($attributes->get('id'))->toBe('hello');
    });
    
    test('Returns an empty string when an attribute does not exist', function() {
        $attributes = new Attributes(['id' => 'hello']);
        expect($attributes->get('class'))->toBe('');
    });
});

describe('merge', function() {
    test('Can merge attributes', function() {
        $attributes = new Attributes(['id' => 'hello']);
        $attributes->merge(['active' => true]);
        expect($attributes->all())->toBe(['id' => 'hello', 'active' => true]);
    });

    it('Does not overwrite existing attributes', function() {
        $attributes = new Attributes(['id' => 'hello']);
        $attributes->merge(['id' => 'world']);
        expect($attributes->all())->toBe(['id' => 'hello']);
    });
    
    it("Is chainable", function() {
        $attributes = new Attributes(['id' => 'hello']);
        expect($attributes->merge(['active' => true]))->toBe($attributes);
    });

    describe('merge class attribute', function() {
        test('Appends to existing class attribute', function() {
            $attributes = new Attributes(['class' => 'hello']);
            $attributes->merge(['class' => 'world']);
            expect($attributes->all())->toBe(['class' => 'hello world']);
        });

        test('Removes duplicate classes', function() {
            $attributes = new Attributes(['class' => 'hello']);
            $attributes->merge(['class' => 'hello']);
            expect($attributes->all())->toBe(['class' => 'hello']);
        });
    });

    describe('merge style attribute', function() {
        test('Appends to existing style attribute', function() {
            $attributes = new Attributes(['style' => 'color: red;']);
            $attributes->merge(['style' => 'background-color: blue;']);
            expect($attributes->all())->toBe(['style' => 'color: red; background-color: blue;']);
        });

        it('Adds missing semicolons', function() {
            $attributes = new Attributes(['style' => 'color: red']);
            $attributes->merge(['style' => 'background-color: blue']);
            expect($attributes->all())->toBe(['style' => 'color: red; background-color: blue;']);
        });

        test('Removes duplicate styles', function() {
            $attributes = new Attributes(['style' => 'color: red;']);
            $attributes->merge(['style' => 'color: red;']);
            expect($attributes->all())->toBe(['style' => 'color: red;']);
        });
    });
});

describe('class', function() {
    test('Merges classes', function() {
        $attributes = new Attributes(['class' => 'hello']);
        $attributes->class(['world']);
        expect($attributes->all())->toBe(['class' => 'hello world']);
    });

    test('Conditionally merges classes based on a condition', function() {
        $attributes = new Attributes(['class' => 'hello']);
        $attributes->class(['world' => false, 'foo' => true]);
        expect($attributes->all())->toBe(['class' => 'hello foo']);
    });

    test("Is chainable", function() {
        $attributes = new Attributes(['class' => 'hello']);
        expect($attributes->class(['world']))->toBe($attributes);
    });
});

describe('style', function() {
    test('Merges styles', function() {
        $attributes = new Attributes(['style' => 'color: red;']);
        $attributes->style(['background-color: blue;']);
        expect($attributes->all())->toBe(['style' => 'color: red; background-color: blue;']);
    });

    test('Conditionally merges styles based on a condition', function() {
        $attributes = new Attributes(['style' => 'color: red;']);
        $attributes->style(['background-color: blue;' => false, 'font-size: 12px;' => true]);
        expect($attributes->all())->toBe(['style' => 'color: red; font-size: 12px;']);
    });

    test("Is chainable", function() {
        $attributes = new Attributes(['style' => 'color: red;']);
        expect($attributes->style(['background-color: blue;']))->toBe($attributes);
    });
});

describe('except', function() {
    test("Returns a new instance of Attributes", function() {
        $attributes = new Attributes(['id' => 'hello', 'class' => 'world']);
        expect($attributes->except(['id']))->toBeInstanceOf(Attributes::class);
        expect($attributes->except(['id']))->not->toBe($attributes);
    });

    test('Removes attributes', function() {
        $attributes = new Attributes(['id' => 'hello', 'class' => 'world']);
        expect($attributes->except(['id'])->all())->toBe(['class' => 'world']);
    });

    test('Does not modify the original instance', function() {
        $attributes = new Attributes(['id' => 'hello', 'class' => 'world']);
        $attributes->except(['id']);
        expect($attributes->all())->toBe(['id' => 'hello', 'class' => 'world']);
    });
});

describe('only', function() {
    test('Keeps only the specified attributes', function() {
        $attributes = new Attributes(['id' => 'hello', 'class' => 'world']);
        expect($attributes->only(['id'])->all())->toBe(['id' => 'hello']);
    });

    test("Returns a new Attributes instance", function() {
        $attributes = new Attributes(['id' => 'hello', 'class' => 'world']);
        expect($attributes->only(['id']))->toBeInstanceOf(Attributes::class);
        expect($attributes->only(['id']))->not->toBe($attributes);
    });
});

describe('__toString', function() {
    test('Returns a string of the attributes', function() {
        $attributes = new Attributes(['id' => 'hello', 'class' => 'world']);
        expect($attributes->__toString())->toBe('id="hello" class="world"');
    });
    
    test('Escapes html special characters', function() {
        $attributes = new Attributes(['id' => 'hello', 'class' => 'world', 'title' => '"Hello & World"']);
        expect($attributes->__toString())->toBe('id="hello" class="world" title="&quot;Hello &amp; World&quot;"');
    });
    
    test('Removes boolean attributes that are false', function() {
        $attributes = new Attributes(['id' => 'hello', 'class' => 'world', 'disabled' => false]);
        expect($attributes->__toString())->toBe('id="hello" class="world"');
    });

    test('Display boolean attributes correctly', function() {
        $attributes = new Attributes(['id' => 'hello', 'class' => 'world', 'disabled' => true]);
        expect($attributes->__toString())->toBe('id="hello" class="world" disabled');
    });
});