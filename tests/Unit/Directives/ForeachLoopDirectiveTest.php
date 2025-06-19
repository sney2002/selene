<?php

use Selene\Directives\ForeachLoopDirective;
use Selene\Node\DirectiveNode;

test('Render @foreach directive', function () {
    $directive = new ForeachLoopDirective();
    expect($directive->render(new DirectiveNode('foreach', '$array as $value')))->toBe('<?php foreach ($array as $value): ?>');
    expect($directive->render(new DirectiveNode('endforeach', '')))->toBe('<?php endforeach; ?>');
});