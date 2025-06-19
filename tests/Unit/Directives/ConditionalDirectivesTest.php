<?php

use Selene\Directives\ConditionalDirectives;
use Selene\Node\DirectiveNode;


test('Should render @if, @elseif, @else and @endif directive', function () {
    $directive = new ConditionalDirectives();
    expect($directive->render(new DirectiveNode('if', 'true')))->toBe('<?php if (true): ?>');
    expect($directive->render(new DirectiveNode('elseif', 'true')))->toBe('<?php elseif (true): ?>');
    expect($directive->render(new DirectiveNode('else', 'true')))->toBe('<?php else: ?>');
    expect($directive->render(new DirectiveNode('endif', '')))->toBe('<?php endif; ?>');
});

test('Render @unless directive', function () {
    $directive = new ConditionalDirectives();
    expect($directive->render(new DirectiveNode('unless', 'true')))->toBe('<?php if (! (true)): ?>');
    expect($directive->render(new DirectiveNode('endunless', '')))->toBe('<?php endif; ?>');
});

test('Render @isset directive', function () {
    $directive = new ConditionalDirectives();
    expect($directive->render(new DirectiveNode('isset', 'true')))->toBe('<?php if (isset(true)): ?>');
    expect($directive->render(new DirectiveNode('endisset', '')))->toBe('<?php endif; ?>');
});

test('Render @empty directive', function () {
    $directive = new ConditionalDirectives();
    expect($directive->render(new DirectiveNode('empty', 'true')))->toBe('<?php if (empty(true)): ?>');
    expect($directive->render(new DirectiveNode('endempty', '')))->toBe('<?php endif; ?>');
});
