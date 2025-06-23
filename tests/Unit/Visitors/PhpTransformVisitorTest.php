<?php

use Selene\Visitor\PhpTransformVisitor;
use Selene\Parser;

expect()->extend('toCompile', function ($result) {
    $parser = new Parser($this->value);
    $nodes = $parser->parse();
    $visitor = new PhpTransformVisitor();

    return expect($visitor->render($nodes))->toBe($result);
});

test('Compiles a comment', function () {
    $template = '{{-- This is a comment --}}';

    expect($template)->toCompile('<?php /* This is a comment */ ?>');
});

test('Compiles an echo directive', function () {
    $template = '{{ $variable }}';

    expect($template)->toCompile('<?php echo e($variable); ?>');
});


test('Compiles an if directive', function () {
    $template = '@if(true)
        <p>True</p>
    @endif';

    expect($template)->toCompile('<?php if (true): ?>
        <p>True</p>
    <?php endif; ?>');
});

test('Compiles an if, elseif, else and endif directive', function () {
    $template = '@if(true)
        <p>True</p>
    @elseif(false)
        <p>False</p>
    @else
        <p>Unknown</p>
    @endif';

    expect($template)->toCompile('<?php if (true): ?>
        <p>True</p>
    <?php elseif (false): ?>
        <p>False</p>
    <?php else: ?>
        <p>Unknown</p>
    <?php endif; ?>');
});

test('Compiles an unless directive', function () {
    $template = '@unless(true)
        <p>True</p>
    @endunless';

    expect($template)->toCompile('<?php if (! (true)): ?>
        <p>True</p>
    <?php endif; ?>');
});

test('Compiles an empty directive', function () {
    $template = '@empty(true)
        <p>True</p>
    @endempty';

    expect($template)->toCompile('<?php if (empty(true)): ?>
        <p>True</p>
    <?php endif; ?>');
});

test('Compiles an isset directive', function () {
    $template = '@isset(true)
        <p>True</p>
    @endisset';

    expect($template)->toCompile('<?php if (isset(true)): ?>
        <p>True</p>
    <?php endif; ?>');
});

test('Compiles a foreach directive', function () {
    $template = '@foreach($array as $item)
        <p>{{ $item }}</p>
    @endforeach';

    expect($template)->toCompile('<?php foreach ($array as $item): ?>
        <p><?php echo e($item); ?></p>
    <?php endforeach; ?>');
});


test('Compiles a for directive', function () {
    $template = '@for($i = 0; $i < 10; $i++)
        <p>{{ $i }}</p>
    @endfor';

    expect($template)->toCompile('<?php for ($i = 0; $i < 10; $i++): ?>
        <p><?php echo e($i); ?></p>
    <?php endfor; ?>');
});

test('Compiles a while directive', function () {
    $template = '@while(true)
        <p>True</p>
    @endwhile';

    expect($template)->toCompile('<?php while (true): ?>
        <p>True</p>
    <?php endwhile; ?>');
});

test('Compiles a forelse directive', function () {
    $template = '@forelse($array as $item)
        <p>{{ $item }}</p>
    @empty
        <p>Empty</p>
    @endforelse';

    expect($template)->toCompile('<?php if (!empty($array)): foreach ($array as $item): ?>
        <p><?php echo e($item); ?></p>
    <?php endforeach; else: ?>
        <p>Empty</p>
    <?php endif; ?>');
});

test('Compiles a switch directive', function () {
    $template = '@switch($variable)
        @case(1)
            <p>One</p>
        @break
        @case(2)
            <p>Two</p>
        @break
        @default
            <p>Default</p>
        @endswitch';

    expect($template)->toCompile('<?php switch ($variable):
        case (1): ?>
            <p>One</p>
        <?php break; ?>
        <?php case (2): ?>
            <p>Two</p>
        <?php break; ?>
        <?php default: ?>
            <p>Default</p>
        <?php endswitch; ?>');
});

test('Compiles a boolean attribute directive', function () {
    $template = '<input type="checkbox" @checked(true)>';
    expect($template)->toCompile('<input type="checkbox" <?php if (true): echo \'checked\'; endif; ?>>');
});

test('Throws an error if a directive is not closed', function () {
    $template = '@if(true)
        <p>True</p>
    @elseif(false)
        <p>False</p>
    @else
        <p>Unknown</p>';

    expect(fn () => expect($template)->toCompile(''))->toThrow(new \ParseError('Directive @if is not closed on line 1'));
});

test('Throws an error if a directive is closed in the wrong order', function () {
    $template = '@if(true)
    @foreach($array as $item)
        <p>True</p>
    @endif';

    expect(fn () => expect($template)->toCompile(''))->toThrow(new \ParseError('Expected @endforeach, got @endif on line 4'));
});

test('Throws an error if the first directive found is not valid', function () {
    $template = 'content
    @endif';

    expect(fn () => expect($template)->toCompile(''))->toThrow(new \ParseError('Expected @if, got @endif on line 2'));
});