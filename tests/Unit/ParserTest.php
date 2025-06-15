<?php

use Selene\Parser;

test('parses a verbatim string', function () {
    $template = 'Hello, world!';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::VERBATIM,
            'content' => 'Hello, world!'
        ]
    ]);
});

test('parses a template with a single interpolation', function () {
    $template = 'Hello, {{ name }}!';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::VERBATIM,
            'content' => 'Hello, '
        ],
        [
            'type' => Parser::INTERPOLATION,
            'content' => '{{ name }}'
        ],
        [
            'type' => Parser::VERBATIM,
            'content' => '!'
        ]
    ]);
});

test('parses string interpolation with curly braces inside double quotes', function () {
    $template = 'Hello, {{ "}" }}!';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::VERBATIM,
            'content' => 'Hello, '
        ],
        [
            'type' => Parser::INTERPOLATION,
            'content' => '{{ "}" }}'
        ],
        [
            'type' => Parser::VERBATIM,
            'content' => '!'
        ]
    ]);
});

test("parses string interpolation with curly braces inside single quotes", function () {
    $template = "Hello, {{ '}' }}!";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::VERBATIM,
            'content' => 'Hello, '
        ],
        [
            'type' => Parser::INTERPOLATION,
            'content' => "{{ '}' }}"
        ],
        [
            'type' => Parser::VERBATIM,
            'content' => '!'
        ]
    ]);
});