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

test('parse string with zeros', function () {
    $template = "007";
    $parser = new Parser($template);
    $result = $parser->parse();
    
    expect($result)->toBe([
        [
            'type' => Parser::VERBATIM,
            'content' => '007'
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

test('parses a directive after line break', function () {
    $template = "\n@if";
    $parser = new Parser($template);
    $result = $parser->parse();
    
    expect($result)->toBe([
        [
            'type' => Parser::VERBATIM,
            'content' => "\n"
        ],
        [
            'type' => Parser::DIRECTIVE,
            'name' => 'if',
            'parameters' => ''
        ],
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

test('parses directives with nested parentheses', function () {
    $template = "@if((\$condition))";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::DIRECTIVE,
            'name' => 'if',
            'parameters' => "(\$condition)"
        ]
    ]);
});

test('parses directives with parentheses inside single quotes', function () {
    $template = "@if(')')";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::DIRECTIVE,
            'name' => 'if',
            'parameters' => "')'"
        ]
    ]);
});

test('parses directives with parentheses inside double quotes', function () {
    $template = '@if(")")';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::DIRECTIVE,
            'name' => 'if',
            'parameters' => '")"'
        ]
    ]);
});

test('parses blade comments', function () {
    $template = '{{-- blade comment --}}';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMMENT,
            'content' => ' blade comment '
        ]
    ]);
});

test('parses a self closing component tag', function () {
    $template = '<x-component />';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [],
            'children' => []
        ]
    ]);
});

test('parses a self closing component without spaces', function () {
    $template = "<x-component/>";
    $parser = new Parser($template);
    $result = $parser->parse();
    
    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [],
            'children' => []
        ]
    ]);
});

test('parses component after line break', function () {
    $template = "\n<x-component></x-component>";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::VERBATIM,
            'content' => "\n"
        ],
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [],
            'children' => []
        ]
    ]);
});

test('parses component with whitespaces inside the tag', function () {
    $template = "<x-component\n \t \r\n/>";
    $parser = new Parser($template);
    $result = $parser->parse();
    
    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [],
            'children' => []
        ]
    ]);
});

test('parses a self closing component tag mixed with content', function () {
    $template = '<x-component />Hello, world!';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [],
            'children' => []
        ],
        [
            'type' => Parser::VERBATIM,
            'content' => 'Hello, world!'
        ]
    ]);
});

test('parses an empty component tag', function () {
    $template = '<x-component></x-component>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [],
            'children' => []
        ]
    ]);
});

test('parses a component with content', function () {
    $template = '<x-component>Hello, {{ $name }}!</x-component>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [],
            'children' => [
                [
                    'type' => Parser::VERBATIM,
                    'content' => 'Hello, '
                ],
                [
                    'type' => Parser::INTERPOLATION,
                    'content' => ' $name '
                ],
                [
                    'type' => Parser::VERBATIM,
                    'content' => '!'
                ]
            ]
        ]
    ]);
});

test('parses a component with html content', function () {
    $template = '<x-component><div>Hello, world!</div></x-component>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [],
            'children' => [
                [
                    'type' => Parser::VERBATIM,
                    'content' => '<div>Hello, world!</div>'
                ]
            ]
        ]
    ]);
});

test('parses nested components', function () {
    $template = '<x-component><x-child>Hello, world!</x-child></x-component>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [],
            'children' => [
                [
                    'type' => Parser::COMPONENT,
                    'name' => 'child',
                    'attributes' => [],
                    'children' => [
                        [
                            'type' => Parser::VERBATIM,
                            'content' => 'Hello, world!'
                        ]
                    ]
                ]
            ]
        ]
    ]);
});

test('parses nested components of the same name', function () {
    $template = '<x-component><x-component>Hello, world!</x-component></x-component>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [],
            'children' => [
                [
                    'type' => Parser::COMPONENT,
                    'name' => 'component',
                    'attributes' => [],
                    'children' => [
                        [
                            'type' => Parser::VERBATIM,
                            'content' => 'Hello, world!'
                        ]
                    ]
                ]
            ]
        ]
    ]);
});

test('parses component with spaces before closing bracket', function () {
    $template = '<x-component></x-component>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [],
            'children' => []
        ]
    ]);
});

test('parses component with attribute', function () {
    $template = '<x-component name="John" />';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [
                'name' => 'John'
            ],
            'children' => []
        ]
    ]);
});

test('parses component with unquoted attribute value', function () {
    $template = '<x-component name=John />';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [
                'name' => 'John'
            ],
            'children' => []
        ]
    ]);
});

test('parses attributes with spaces around the equal sign', function () {
    $template = "<x-component name\r\n\t =\r\n\t 'John' />";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [
                'name' => 'John'
            ],
            'children' => []
        ]
    ]);
});

test('parses boolean attributes', function () {
    $template = '<x-component disabled ></x-component>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [
                'disabled' => ''
            ],
            'children' => []
        ]
    ]);
});

test('parses multiple attributes', function () {
    $template = '<x-component name="John" disabled class="bg-red-500" />';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toBe([
        [
            'type' => Parser::COMPONENT,
            'name' => 'component',
            'attributes' => [
                'name' => 'John',
                'disabled' => '',
                'class' => 'bg-red-500'
            ],
            'children' => []
        ]
    ]);
});