<?php

use Selene\Parser;
use Selene\Node\VerbatimNode;
use Selene\Node\InterpolationNode;
use Selene\Node\DirectiveNode;
use Selene\Node\CommentNode;
use Selene\Node\ComponentNode;

test('parses a verbatim string', function () {
    $template = 'Hello, world!';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new VerbatimNode('Hello, world!')
    ]);
});

test('parse string with zeros', function () {
    $template = "007";
    $parser = new Parser($template);
    $result = $parser->parse();
    
    expect($result)->toEqualCanonicalizing([
        new VerbatimNode('007')
    ]);
});

test('parses a template with a single interpolation', function () {
    $template = 'Hello, {{ name }}!';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new VerbatimNode('Hello, '),
        new InterpolationNode(' name '),
        new VerbatimNode('!')
    ]);
});

test('parses string interpolation with curly braces inside double quotes', function () {
    $template = 'Hello, {{ "}" }}!';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new VerbatimNode('Hello, '),
        new InterpolationNode(' "}" '),
        new VerbatimNode('!')
    ]);
});

test("parses string interpolation with curly braces inside single quotes", function () {
    $template = "Hello, {{ '}' }}!";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new VerbatimNode('Hello, '),
        new InterpolationNode(" '}' "),
        new VerbatimNode('!')
    ]);
});

test('parses string interpolation with escaped quotes (single quotes)', function () {
    $template = "Hello, {{ '\'}' }}!";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new VerbatimNode('Hello, '),
        new InterpolationNode(" '\'}' "),
        new VerbatimNode('!')
    ]);
});

test('parses string interpolation with escaped quotes (double quotes)', function () {
    $template = 'Hello, {{ "\"}" }}!';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new VerbatimNode('Hello, '),
        new InterpolationNode(' "\"}" '),
        new VerbatimNode('!')
    ]);
});


test('parses a directive', function () {
    $template = '@if';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new DirectiveNode('if')
    ]);
});

test('parses a directive after line break', function () {
    $template = "\n@if";
    $parser = new Parser($template);
    $result = $parser->parse();
    
    expect($result)->toEqualCanonicalizing([
        new VerbatimNode("\n"),
        new DirectiveNode('if')
    ]);
});

test('parses a directive with a space', function () {
    $template = '@if ';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new DirectiveNode('if')
    ]);
});

test('line breaks after a directive should be preserved', function () {
    $template = "@if\ncontent";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new DirectiveNode('if'),
        new VerbatimNode("\ncontent")
    ]);
});

test('non whitespace characters after a directive should be preserved', function () {
    $template = "@if content";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new DirectiveNode('if'),
        new VerbatimNode("content")
    ]);
});

test('parses a directive with a space and a newline', function () {
    $template = "@if \n";
    $parser = new Parser($template);
    $result = $parser->parse();
    
    expect($result)->toEqualCanonicalizing([
        new DirectiveNode('if'),
        new VerbatimNode("\n")
    ]);
});

test('parses directives with parameters', function () {
    $template = '@if($condition)';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new DirectiveNode('if', '$condition')
    ]);
});

test('parses directives with parameters and a newline', function () {
    $template = "@if(\$condition)\n";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new DirectiveNode('if', '$condition'),
        new VerbatimNode("\n")
    ]);
});

test('parses directives line breaks inside parentheses', function () {
    $template = "@if (\n\$condition\n)\n";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new DirectiveNode('if', '$condition'),
        new VerbatimNode("\n")
    ]);
});

test('parses directives with nested parentheses', function () {
    $template = "@if((\$condition))";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new DirectiveNode('if', "(\$condition)")
    ]);
});

test('parses directives with parentheses inside single quotes', function () {
    $template = "@if(')')";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new DirectiveNode('if', "')'")
    ]);
});

test('parses directives with parentheses inside double quotes', function () {
    $template = '@if(")")';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new DirectiveNode('if', '")"')
    ]);
});

test('parses blade comments', function () {
    $template = '{{-- blade comment --}}';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new CommentNode(' blade comment ')
    ]);
});

test('parses a self closing component tag', function () {
    $template = '<x-component />';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('component', [], [])
    ]);
});

test('parses a self closing component without spaces', function () {
    $template = "<x-component/>";
    $parser = new Parser($template);
    $result = $parser->parse();
    
    expect($result)->toEqualCanonicalizing([
        new ComponentNode('component', [], [])
    ]);
});

test('parses component after line break', function () {
    $template = "\n<x-component></x-component>";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new VerbatimNode("\n"),
        new ComponentNode('component', [], [])
    ]);
});

test('parses component with whitespaces inside the tag', function () {
    $template = "<x-component\n \t \r\n/>";
    $parser = new Parser($template);
    $result = $parser->parse();
    
    expect($result)->toEqualCanonicalizing([
        new ComponentNode('component', [], [])
    ]);
});

test('parses a self closing component tag mixed with content', function () {
    $template = '<x-component />Hello, world!';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('component', [], []),
        new VerbatimNode('Hello, world!')
    ]);
});

test('parses an empty component tag', function () {
    $template = '<x-component></x-component>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('component', [], [])
    ]);
});

test('parses a component with content', function () {
    $template = '<x-component>Hello, {{ $name }}!</x-component>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('component', [], [
            new VerbatimNode('Hello, '),
            new InterpolationNode(' $name '),
            new VerbatimNode('!')
        ])
    ]);
});

test('parses a component with html content', function () {
    $template = '<x-component><div>Hello, world!</div></x-component>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('component', [], [
            new VerbatimNode('<div>Hello, world!</div>')
        ])
    ]);
});

test('parses nested components', function () {
    $template = '<x-parent><x-child>Hello</x-child></x-parent>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('parent', [], [
            new ComponentNode('child', [], [
                new VerbatimNode('Hello')
            ])
        ])
    ]);
});

test('parses nested components of the same name', function () {
    $template = '<x-parent><x-parent>Hello</x-parent></x-parent>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('parent', [], [
            new ComponentNode('parent', [], [
                new VerbatimNode('Hello')
            ])
        ])
    ]);
});

test('parses component with spaces before closing bracket', function () {
    $template = '<x-parent></x-parent>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('parent', [], [])
    ]);
});

test('parses component with attribute', function () {
    $template = '<x-parent name="John" />';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('parent', ['name' => 'John'], [])
    ]);
});

test('parses component with unquoted attribute value', function () {
    $template = '<x-parent name=John />';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('parent', ['name' => 'John'], [])
    ]);
});

test('parses attributes with spaces around the equal sign', function () {
    $template = "<x-parent name\r\n\t =\r\n\t 'John' />";
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('parent', ['name' => 'John'], [])
    ]);
});

test('parses boolean attributes', function () {
    $template = '<x-parent disabled ></x-parent>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('parent', ['disabled' => ''], [])
    ]);
});

test('parses multiple attributes', function () {
    $template = '<x-parent name="John" disabled class="bg-red-500" />';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('parent', ['name' => 'John', 'disabled' => '', 'class' => 'bg-red-500'], [])
    ]);
});

test('whitespaces between attributes', function () {
    $whiteSpaceChars = [' ', "\n", "\t", "\r"];

    foreach ($whiteSpaceChars as $whiteSpaceChar) {
        $template = "<x-parent{$whiteSpaceChar}enabled{$whiteSpaceChar}name='John'{$whiteSpaceChar}class='bg-red-500'{$whiteSpaceChar}/>";
        $parser = new Parser($template);
        $result = $parser->parse();

        expect($result)->toEqualCanonicalizing([
            new ComponentNode('parent', ['enabled' => '', 'name' => 'John', 'class' => 'bg-red-500'], [])
        ]);
    }
});

test('parses components with nested interpolations', function () {
    $template = '<x-parent>Hello {{ name }}!</x-parent>';
    $parser = new Parser($template);
    $result = $parser->parse();

    expect($result)->toEqualCanonicalizing([
        new ComponentNode('parent', [], [
            new VerbatimNode('Hello '),
            new InterpolationNode(' name '),
            new VerbatimNode('!')
        ])
    ]);
});