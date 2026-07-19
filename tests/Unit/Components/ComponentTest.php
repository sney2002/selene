<?php

namespace Tests\Unit\Components;

use Selene\Components\Component;
use Selene\Components\Attributes;

test('name', function() {
    $component = new Component('test');
    expect($component->getName())->toBe('test');
});

describe('attributes', function() {
    test('must be an instance of Attributes', function() {
        $component = new Component('test');
        expect($component->getAttributes())->toBeInstanceOf(Attributes::class);
    });

    test('can be set', function() {
        $component = new Component('test');
        $component->setAttributes(['id' => 'test']);
        expect($component->getAttributes()->all())->toBe(['id' => 'test']);
    });

    test('it merge any Attributes instance into a single instance', function() {
        $component = new Component('test');
        $component->setAttributes([
            'id' => 'test',
            'attributes' => new Attributes(['class' => 'test'])
        ]);
        expect($component->getAttributes()->all())->toBe(['id' => 'test', 'class' => 'test']);
    });
});


describe('props', function() {
    test('must be an array', function() {
        $component = new Component('test');
        expect($component->getProps())->toBe([]);
    });

    test('can be set', function() {
        $component = new Component('test');
        $component->setProps(['id' => 'test']);
        expect($component->getProps())->toBe(['id' => 'test']);
    });

    test('Properties without a default value should be null', function() {
        $component = new Component('test');
        $component->setProps(['id' => 'test', 'undefined']);
        expect($component->getProps())->toBe(['id' => 'test', 'undefined' => null]);
    });

    test('properties values are overwritten by attributes of the same name', function() {
        $component = new Component('test', attributes: ['id' => 'test2']);
        $component->setProps(['id' => 'test']);
        expect($component->getProps())->toBe(['id' => 'test2']);
    });

    test('camelCase properties values are overwritten by kebab-case attributes', function() {
        $component = new Component('test', attributes: ['kebab-case' => 'test2']);
        $component->setProps(['kebabCase' => 'test']);
        expect($component->getProps())->toBe(['kebabCase' => 'test2']);
    });

    test('attributes included in props must be removed from the attributes', function() {
        $component = new Component('test', attributes: ['id' => 'test2', 'kebab-case' => 'test3']);
        $component->setProps(['id' => 'test', 'kebabCase' => 'test4']);
        expect($component->getAttributes()->all())->toBe([]);
    });

    test('slots must be included in the properties', function() {
        $component = new Component('test', slots: ['slot' => 'test']);
        $component->setProps(['id' => 'test2']);
        expect($component->getProps())->toBe(['id' => 'test2', 'slot' => 'test']);
    });
});
