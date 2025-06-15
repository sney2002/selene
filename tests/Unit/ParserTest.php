<?php

use Selene\Parser;

test('parses a verbatim string', function () {
    $parser = new Parser();
    $template = 'Hello, world!';
    $result = $parser->parse($template);

    expect($result)->toBe([
        [
            'type' => Parser::VERBATIM,
            'content' => 'Hello, world!'
        ]
    ]);
});