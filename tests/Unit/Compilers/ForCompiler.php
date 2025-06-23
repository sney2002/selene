<?php

use Selene\Compilers\ForCompiler;
use Selene\Nodes\DirectiveNode;

test('Compile @for directive', function () {
    $directive = new ForCompiler();
    expect($directive->compile(new DirectiveNode('for', '$i = 0; $i < count($array); $i++')))->toBe('<?php for ($i = 0; $i < count($array); $i++): ?>');
    expect($directive->compile(new DirectiveNode('endfor', '')))->toBe('<?php endfor; ?>');
});
