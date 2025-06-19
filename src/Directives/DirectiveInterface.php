<?php

namespace Selene\Directives;

use Selene\Node\DirectiveNode;

interface DirectiveInterface {
    public function render(DirectiveNode $directive) : ?string;
}