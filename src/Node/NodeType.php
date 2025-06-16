<?php

namespace Selene\Node;

enum NodeType: string {
    case VERBATIM = 'verbatim';
    case INTERPOLATION = 'interpolation';
    case DIRECTIVE = 'directive';
    case COMMENT = 'comment';
    case COMPONENT = 'component';
} 