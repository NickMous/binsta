<?php

namespace NickMous\Binsta\Internals\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Singleton
{
    public function __construct()
    {
    }
}
