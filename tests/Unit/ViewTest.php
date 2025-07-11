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

    test('Throws an exception if a section is closed without being opened', function() {
        $view = new View();

        expect(fn() => $view->endSection())->toThrow(new \InvalidArgumentException('Cannot end a section without opening one'));
    });

    test('yield default value if the section is not defined', function() {
        $view = new View();

        expect($view->yield('section', 'Hello, world!'))->toBe('Hello, world!');
    });
});