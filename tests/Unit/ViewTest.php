<?php

use Selene\View;

describe('sections', function() {
    test('returns an empty string if the section is not defined', function() {
        $view = new View();

        expect($view->yield('section'))->toBe('');
    });

    test('stores the content of a section passed as a string', function() {
        $view = new View();
        $view->section('section', 'Hello, world!');
        expect($view->yield('section'))->toBe('Hello, world!');
    });

    test('stores the content of a section defined between @section and @endsection', function() {
        $view = new View();
        $view->section('section');
        echo 'Hello, world!';
        $view->endSection();
        expect($view->yield('section'))->toBe('Hello, world!');
    });

    test('can store multiple sections', function() {
        $view = new View();

        $view->section('section1');
        echo 'Hola mundo!';
        $view->endSection();

        $view->section('section2');
        echo 'Hello, world!';
        $view->endSection();

        expect($view->yield('section1'))->toBe('Hola mundo!');
        expect($view->yield('section2'))->toBe('Hello, world!');
    });

    test('Sections with the same name are overwritten', function() {
        $view = new View();
        
        $view->section('section');
        echo 'Hello, world!';
        $view->endSection();
        
        $view->section('section');
        echo 'Hola mundo!';
        $view->endSection();

        expect($view->yield('section'))->toBe('Hola mundo!');
    });

    test('can check if a section is defined', function() {
        $view = new View();
        $view->section('section', 'Hello, world!');

        expect($view->hasSection('section'))->toBeTrue();
        expect($view->hasSection('section2'))->toBeFalse();
    });

    test('can include the content of the parent section', function() {
        $view = new View();
        $view->section('section', '[parent]');
       
        $view->section('section');
        $view->parentContent();
        echo '[child]';
        $view->endSection();

        $view->section('section');
        $view->parentContent();
        echo '[child2]';
        $view->endSection();

        expect($view->yield('section'))->toBe('[parent][child][child2]');
    });

    test('allow calling parentContent() the first time a section is defined', function() {
        $view = new View();
        $view->section('section');
        $view->parentContent();
        echo '[child1]';
        $view->endSection();

        $view->section('section');
        $view->parentContent();
        echo '[child2]';
        $view->endSection();
        
        expect($view->yield('section'))->toBe('[child1][child2]');
    });

    test('Throws an exception if a section is closed without being opened', function() {
        $view = new View();

        expect(fn() => $view->endSection())->toThrow(new \InvalidArgumentException('Cannot end a section without opening one'));
    });

    test('yield default value if the section is not defined', function() {
        $view = new View();

        expect($view->yield('section', 'Hello, world!'))->toBe('Hello, world!');
    });
});

describe('stacks', function() {
    test('can push content to a stack with a string', function() {
        $view = new View();
        $view->push('stack', 'Hello, world!');
        expect($view->yieldStack('stack'))->toBe('Hello, world!');
    });

    test('can push content between @push and @endpush', function() {
        $view = new View();
        $view->push('stack');
        echo 'Hello, world!';
        $view->endPush();
        expect($view->yieldStack('stack'))->toBe('Hello, world!');
    });

    test('can push multiple times to the same stack', function() {
        $view = new View();

        $view->push('stack');
        echo 'Hello, ';
        $view->endPush();

        $view->push('stack');
        echo 'world!';
        $view->endPush();

        expect($view->yieldStack('stack'))->toBe('Hello, world!');
    });

    test('can prepend content to a stack with a string', function() {
        $view = new View();
        $view->prepend('stack', 'Hello, world!');
        expect($view->yieldStack('stack'))->toBe('Hello, world!');
    });

    test('can prepend content to a stack between @push and @endpush', function() {
        $view = new View();
        $view->prepend('stack');
        echo 'Hello, world!';
        $view->endPrepend();
        expect($view->yieldStack('stack'))->toBe('Hello, world!');
    });

    test('can prepend multiple times to the same stack', function() {
        $view = new View();

        $view->prepend('stack');
        echo 'world!';
        $view->endPrepend();

        $view->prepend('stack');
        echo 'Hello, ';
        $view->endPrepend();

        expect($view->yieldStack('stack'))->toBe('Hello, world!');
    });

    test('can combine push and prepend', function() {
        $view = new View();

        $view->push('stack');
        echo 'Hello, ';
        $view->endPush();

        $view->push('stack');
        echo 'world!';
        $view->endPush();

        $view->prepend('stack');
        echo 'mundo!';
        $view->endPrepend();

        $view->prepend('stack');
        echo '!Hola, ';
        $view->endPrepend();

        expect($view->yieldStack('stack'))->toBe('!Hola, mundo!Hello, world!');
    });

    test('yieldStack returns an empty string if the stack is not defined', function() {
        $view = new View();
        expect($view->yieldStack('stack'))->toBe('');
    });

    test('Throws an exception if a push is closed without being opened', function() {
        $view = new View();

        expect(fn() => $view->endPush())->toThrow(new \InvalidArgumentException('Cannot end a push without opening one'));
    });

    test('Throws an exception if a prepend is closed without being opened', function() {
        $view = new View();
        expect(fn() => $view->endPrepend())->toThrow(new \InvalidArgumentException('Cannot end a prepend without opening one'));
    });
});