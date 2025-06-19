<?php

use Selene\Directives\WhileLoopDirective;
use Selene\Node\DirectiveNode;

test('Render @while directive', function () {
    $directive = new WhileLoopDirective();
    expect($directive->render(new DirectiveNode('while', '$i < count($array)')))->toBe('<?php while ($i < count($array)): ?>');
    expect($directive->render(new DirectiveNode('endwhile', '')))->toBe('<?php endwhile; ?>');
});