<?php
namespace Selene\Nodes;

enum NodeType: string {
    case VERBATIM = 'verbatim';
    case INTERPOLATION = 'interpolation';
    case DIRECTIVE = 'directive';
    case COMMENT = 'comment';
    case COMPONENT = 'component';
} 