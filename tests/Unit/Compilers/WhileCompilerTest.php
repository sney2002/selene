<?php

use Selene\Compilers\WhileCompiler;
use Selene\Nodes\DirectiveNode;

test('Compile @while directive', function () {
    $directive = new WhileCompiler();
    expect($directive->compile(new DirectiveNode('while', '$i < count($array)')))->toBe('<?php while ($i < count($array)): ?>');
    expect($directive->compile(new DirectiveNode('endwhile', '')))->toBe('<?php endwhile; ?>');
});