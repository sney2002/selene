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
            'content' => ' name '
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
            'content' => ' "}" '
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
            'content' => " '}' "
        ],
        [
            'type' => Parser::VERBATIM,
            'content' => '!'
        ]
    ]);
});

test('parses string interpolation with escaped quotes (single quotes)', function () {
    $template = "Hello, {{ '\'}' }}!";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::VERBATIM,
            'content' => 'Hello, '
        ],
        [
            'type' => Parser::INTERPOLATION,
            'content' => " '\'}' "
        ],
        [
            'type' => Parser::VERBATIM,
            'content' => '!'
        ]
    ]);
});

test('parses string interpolation with escaped quotes (double quotes)', function () {
    $template = 'Hello, {{ "\"}" }}!';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::VERBATIM,
            'content' => 'Hello, '
        ],
        [
            'type' => Parser::INTERPOLATION,
            'content' => ' "\"}" '
        ],
        [
            'type' => Parser::VERBATIM,
            'content' => '!'
        ]
    ]);
});


test('parses a directive', function () {
    $template = '@if';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::DIRECTIVE,
            'name' => 'if',
            'parameters' => ''
        ]
    ]);
});

test('parses a directive with a space', function () {
    $template = '@if ';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::DIRECTIVE,
            'name' => 'if',
            'parameters' => ''
        ]
    ]);
});

test('parses a directive with a space and a newline', function () {
    $template = "@if \n";
    $parser = new Parser($template);
    $result = $parser->parse();
    
    expect($result)->toBe([
        [
            'type' => Parser::DIRECTIVE,
            'name' => 'if',
            'parameters' => ''
        ],
        [
            'type' => Parser::VERBATIM,
            'content' => "\n"
        ]
    ]);
});

test('parses directives with parameters', function () {
    $template = '@if($condition)';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::DIRECTIVE,
            'name' => 'if',
            'parameters' => '$condition'
        ]
    ]);
});

test('parses directives with parameters and a newline', function () {
    $template = "@if(\$condition)\n";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::DIRECTIVE,
            'name' => 'if',
            'parameters' => '$condition'
        ],
        [
            'type' => Parser::VERBATIM,
            'content' => "\n"
        ]
    ]);
});

test('parses directives line breaks inside parentheses', function () {
    $template = "@if (\n\$condition\n)\n";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::DIRECTIVE,
            'name' => 'if',
            'parameters' => '$condition'
        ],
        [
            'type' => Parser::VERBATIM,
            'content' => "\n"
        ]
    ]);
});