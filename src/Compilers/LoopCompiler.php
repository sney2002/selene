<?php
namespace Selene\Compilers;

use Selene\Nodes\DirectiveNode;

abstract class LoopCompiler extends DirectiveCompiler {
    public function isClosed() : bool {
        return false;
    }
}