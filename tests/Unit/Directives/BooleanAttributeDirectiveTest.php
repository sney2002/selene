<?php

use Selene\Directives\BooleanAttributeDirective;
use Selene\Node\DirectiveNode;


test('Render @checked directive', function () {
    $directive = new BooleanAttributeDirective();
    $node = new DirectiveNode('checked', 'true');
    expect($directive->render($node))->toBe("<?php if (true): echo 'checked'; endif; ?>");
});

test('Render @selected directive', function () {
    $directive = new BooleanAttributeDirective();
    $node = new DirectiveNode('selected', 'true');
    expect($directive->render($node))->toBe("<?php if (true): echo 'selected'; endif; ?>");
});

test('Render @disabled directive', function () {
    $directive = new BooleanAttributeDirective();
    $node = new DirectiveNode('disabled', 'true');
    expect($directive->render($node))->toBe("<?php if (true): echo 'disabled'; endif; ?>");
});

test('Render @readonly directive', function () {
    $directive = new BooleanAttributeDirective();
    $node = new DirectiveNode('readonly', 'true');
    expect($directive->render($node))->toBe("<?php if (true): echo 'readonly'; endif; ?>");
});

test('Render @required directive', function () {
    $directive = new BooleanAttributeDirective();
    $node = new DirectiveNode('required', 'true');
    expect($directive->render($node))->toBe("<?php if (true): echo 'required'; endif; ?>");
});