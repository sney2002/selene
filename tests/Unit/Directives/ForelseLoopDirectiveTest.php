<?php

use Selene\Directives\ForelseLoopDirective;
use Selene\Node\DirectiveNode;

test('Render @forelse directive', function () {
    $directive = new ForelseLoopDirective();
    expect($directive->render(new DirectiveNode('forelse', '$array as $value')))->toBe('<?php if (!empty($array)): foreach ($array as $value): ?>');
    expect($directive->render(new DirectiveNode('empty', '')))->toBe('<?php endforeach; else: ?>');
    expect($directive->render(new DirectiveNode('endforelse', '')))->toBe('<?php endif; ?>');
});