<?php

namespace Selene;

class Parser
{
    const VERBATIM = 'verbatim';

    public function parse(string $template): array
    {
        return [
            [
                'type' => self::VERBATIM,
                'content' => $template
            ]
        ];
    }
}