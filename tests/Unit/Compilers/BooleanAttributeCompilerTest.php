<?php

use Selene\Compilers\BooleanAttributeCompiler;
use Selene\Nodes\DirectiveNode;

test('Compile @checked directive', function () {
    $directive = new BooleanAttributeCompiler();
    $node = new DirectiveNode('checked', 'true');
    expect($directive->compile($node))->toBe("<?php if (true): echo 'checked'; endif; ?>");
});

test('Compile @selected directive', function () {
    $directive = new BooleanAttributeCompiler();
    $node = new DirectiveNode('selected', 'true');
    expect($directive->compile($node))->toBe("<?php if (true): echo 'selected'; endif; ?>");
});

test('Compile @disabled directive', function () {
    $directive = new BooleanAttributeCompiler();
    $node = new DirectiveNode('disabled', 'true');
    expect($directive->compile($node))->toBe("<?php if (true): echo 'disabled'; endif; ?>");
});

test('Compile @readonly directive', function () {
    $directive = new BooleanAttributeCompiler();
    $node = new DirectiveNode('readonly', 'true');
    expect($directive->compile($node))->toBe("<?php if (true): echo 'readonly'; endif; ?>");
});

test('Compile @required directive', function () {
    $directive = new BooleanAttributeCompiler();
    $node = new DirectiveNode('required', 'true');
    expect($directive->compile($node))->toBe("<?php if (true): echo 'required'; endif; ?>");
});