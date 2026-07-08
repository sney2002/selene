<?php

namespace Tests\Unit\Components;

use Selene\Components\Slot;
use Selene\Components\Attributes;
use Stringable;

test('Is stringable', function() {
    expect(Slot::class)->toImplement(Stringable::class);
});

test('Has a default content', function() {
    $slot = new Slot();
    expect($slot->__toString())->toBe('');
});

test('Has attributes', function() {
    $slot = new Slot('test', ['id' => 'test']);
    expect($slot->attributes)->toBeInstanceOf(Attributes::class);
    expect($slot->attributes->all())->toBe(['id' => 'test']);
});

test('isEmpty', function() {
    expect((new Slot())->isEmpty())->toBeTrue();
    expect((new Slot('test'))->isEmpty())->toBeFalse();
});


test('hasActualContent', function() {
    expect((new Slot('<!-- test -->Content'))->hasActualContent())->toBeTrue();
    expect((new Slot('<!-- test -->'))->hasActualContent())->toBeFalse();
    expect((new Slot('<!--
        multiline
        comment
    -->'))->hasActualContent())->toBeFalse();
    expect((new Slot('<!--
        multiline
        comment
    -->Content'))->hasActualContent())->toBeTrue();
});
