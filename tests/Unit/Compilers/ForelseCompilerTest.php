<?php

use Selene\Compilers\ForelseCompiler;
use Selene\Nodes\DirectiveNode;

test('Compile @forelse directive', function () {
    $directive = new ForelseCompiler();
    expect($directive->compile(new DirectiveNode('forelse', '$array as $value')))->toBe('<?php if (!empty($array)): foreach ($array as $value): ?>');
    expect($directive->compile(new DirectiveNode('empty', '')))->toBe('<?php endforeach; else: ?>');
    expect($directive->compile(new DirectiveNode('endforelse', '')))->toBe('<?php endif; ?>');
});