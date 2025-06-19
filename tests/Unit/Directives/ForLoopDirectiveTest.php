<?php

use Selene\Directives\ForLoopDirective;
use Selene\Node\DirectiveNode;

test('Render @for directive', function () {
    $directive = new ForLoopDirective();
    expect($directive->render(new DirectiveNode('for', '$i = 0; $i < count($array); $i++')))->toBe('<?php for ($i = 0; $i < count($array); $i++): ?>');
    expect($directive->render(new DirectiveNode('endfor', '')))->toBe('<?php endfor; ?>');
});
