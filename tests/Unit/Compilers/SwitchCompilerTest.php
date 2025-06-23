<?php

use Selene\Compilers\SwitchCompiler;
use Selene\Nodes\DirectiveNode;

test('First case must be within the same <?php tag as the switch', function () {
    $directive = new SwitchCompiler();
    expect($directive->compile(new DirectiveNode('switch', 'true')))->toBe('<?php switch (true):');
    expect($directive->compile(new DirectiveNode('case', 'true')))->toBe('case (true): ?>');
});

test('Subsequent cases can be within a new <?php tag', function () {
    $directive = new SwitchCompiler();
    expect($directive->compile(new DirectiveNode('switch', 'true')))->toBe('<?php switch (true):');
    expect($directive->compile(new DirectiveNode('case', 'true')))->toBe('case (true): ?>');
    expect($directive->compile(new DirectiveNode('case', 'true')))->toBe('<?php case (true): ?>');
});

test('Render @switch directive', function () {
    $directive = new SwitchCompiler();
    expect($directive->compile(new DirectiveNode('switch', 'true')))->toBe('<?php switch (true):');
    expect($directive->compile(new DirectiveNode('case', 'true')))->toBe('case (true): ?>');
    expect($directive->compile(new DirectiveNode('case', 'true')))->toBe('<?php case (true): ?>');
    expect($directive->compile(new DirectiveNode('default', 'true')))->toBe('<?php default: ?>');
    expect($directive->compile(new DirectiveNode('break', 'true')))->toBe('<?php break; ?>');
    expect($directive->compile(new DirectiveNode('endswitch', '')))->toBe('<?php endswitch; ?>');
});

test('Can render nested switch statements', function () {
    $directive = new SwitchCompiler();
    expect($directive->compile(new DirectiveNode('switch', 'true')))->toBe('<?php switch (true):');
        expect($directive->compile(new DirectiveNode('case', 'true')))->toBe('case (true): ?>');
            expect($directive->compile(new DirectiveNode('switch', 'true')))->toBe('<?php switch (true):');
                expect($directive->compile(new DirectiveNode('case', 'true')))->toBe('case (true): ?>');
                expect($directive->compile(new DirectiveNode('case', 'true')))->toBe('<?php case (true): ?>');
        expect($directive->compile(new DirectiveNode('endswitch', '')))->toBe('<?php endswitch; ?>');
    expect($directive->compile(new DirectiveNode('case', 'true')))->toBe('<?php case (true): ?>');
    expect($directive->compile(new DirectiveNode('endswitch', '')))->toBe('<?php endswitch; ?>');
});