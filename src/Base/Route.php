<?php

namespace NickMous\MyOwnFramework\Base;

use Attribute;

#[Attribute] readonly class Route
{
    public function __construct(
        private string $path,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
