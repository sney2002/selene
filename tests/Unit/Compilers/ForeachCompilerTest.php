<?php

use Selene\Compilers\ForeachCompiler;
use Selene\Nodes\DirectiveNode;

test('Compile @foreach directive', function () {
    $directive = new ForeachCompiler();
    expect($directive->compile(new DirectiveNode('foreach', '$array as $value')))->toBe('<?php foreach ($array as $value): ?>');
    expect($directive->compile(new DirectiveNode('endforeach', '')))->toBe('<?php endforeach; ?>');
});