<?php

use Selene\Compilers\ConditionalsCompiler;
use Selene\Nodes\DirectiveNode;


test('Compile @if, @elseif, @else and @endif directive', function () {
    $directive = new ConditionalsCompiler();
    expect($directive->compile(new DirectiveNode('if', 'true')))->toBe('<?php if (true): ?>');
    expect($directive->compile(new DirectiveNode('elseif', 'true')))->toBe('<?php elseif (true): ?>');
    expect($directive->compile(new DirectiveNode('else', 'true')))->toBe('<?php else: ?>');
    expect($directive->compile(new DirectiveNode('endif', '')))->toBe('<?php endif; ?>');
});

test('Compile @unless directive', function () {
    $directive = new ConditionalsCompiler();
    expect($directive->compile(new DirectiveNode('unless', 'true')))->toBe('<?php if (! (true)): ?>');
    expect($directive->compile(new DirectiveNode('endunless', '')))->toBe('<?php endif; ?>');
});

test('Compile @isset directive', function () {
    $directive = new ConditionalsCompiler();
    expect($directive->compile(new DirectiveNode('isset', 'true')))->toBe('<?php if (isset(true)): ?>');
    expect($directive->compile(new DirectiveNode('endisset', '')))->toBe('<?php endif; ?>');
});

test('Compile @empty directive', function () {
    $directive = new ConditionalsCompiler();
    expect($directive->compile(new DirectiveNode('empty', 'true')))->toBe('<?php if (empty(true)): ?>');
    expect($directive->compile(new DirectiveNode('endempty', '')))->toBe('<?php endif; ?>');
});
