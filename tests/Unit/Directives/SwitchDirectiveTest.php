<?php

use Selene\Directives\SwitchDirective;
use Selene\Node\DirectiveNode;

test('First case must be within the same <?php tag as the switch', function () {
    $directive = new SwitchDirective();
    expect($directive->render(new DirectiveNode('switch', 'true')))->toBe('<?php switch (true):');
    expect($directive->render(new DirectiveNode('case', 'true')))->toBe('case (true): ?>');
});

test('Subsequent cases can be within a new <?php tag', function () {
    $directive = new SwitchDirective();
    expect($directive->render(new DirectiveNode('switch', 'true')))->toBe('<?php switch (true):');
    expect($directive->render(new DirectiveNode('case', 'true')))->toBe('case (true): ?>');
    expect($directive->render(new DirectiveNode('case', 'true')))->toBe('<?php case (true): ?>');
});

test('Render @switch directive', function () {
    $directive = new SwitchDirective();
    expect($directive->render(new DirectiveNode('switch', 'true')))->toBe('<?php switch (true):');
    expect($directive->render(new DirectiveNode('case', 'true')))->toBe('case (true): ?>');
    expect($directive->render(new DirectiveNode('case', 'true')))->toBe('<?php case (true): ?>');
    expect($directive->render(new DirectiveNode('default', 'true')))->toBe('<?php default: ?>');
    expect($directive->render(new DirectiveNode('break', 'true')))->toBe('<?php break; ?>');
    expect($directive->render(new DirectiveNode('endswitch', '')))->toBe('<?php endswitch; ?>');
});

test('Can render nested switch statements', function () {
    $directive = new SwitchDirective();
    expect($directive->render(new DirectiveNode('switch', 'true')))->toBe('<?php switch (true):');
        expect($directive->render(new DirectiveNode('case', 'true')))->toBe('case (true): ?>');
            expect($directive->render(new DirectiveNode('switch', 'true')))->toBe('<?php switch (true):');
                expect($directive->render(new DirectiveNode('case', 'true')))->toBe('case (true): ?>');
                expect($directive->render(new DirectiveNode('case', 'true')))->toBe('<?php case (true): ?>');
        expect($directive->render(new DirectiveNode('endswitch', '')))->toBe('<?php endswitch; ?>');
    expect($directive->render(new DirectiveNode('case', 'true')))->toBe('<?php case (true): ?>');
    expect($directive->render(new DirectiveNode('endswitch', '')))->toBe('<?php endswitch; ?>');
});