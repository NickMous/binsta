<?php

namespace NickMous\Binsta\Internals\Attributes;

use Attribute;

#[Attribute]
class Priority
{
    public function __construct(public int $value)
    {
    }
}
