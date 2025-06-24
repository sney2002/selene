<?php

use Selene\Compilers\LoopControlCompiler;
use Selene\Nodes\DirectiveNode;

test('can compile continue and break directives', function () {
    $compiler = new LoopControlCompiler();
    $directive = new DirectiveNode('continue');
    $result = $compiler->compile($directive);

    expect($result)->toBe('<?php continue; ?>');
});

test('can compile continue directive with a condition', function () {
    $compiler = new LoopControlCompiler();
    $directive = new DirectiveNode('continue', 'true');
    $result = $compiler->compile($directive);

    expect($result)->toBe('<?php if (true): continue; endif; ?>');
});

test('can compile break directives', function () {
    $compiler = new LoopControlCompiler();
    $directive = new DirectiveNode('break');
    $result = $compiler->compile($directive);

    expect($result)->toBe('<?php break; ?>');
});

test('can compile break directive with a condition', function () {
    $compiler = new LoopControlCompiler();
    $directive = new DirectiveNode('break', 'true');
    $result = $compiler->compile($directive);

    expect($result)->toBe('<?php if (true): break; endif; ?>');
});