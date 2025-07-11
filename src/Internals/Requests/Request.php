<?php

namespace NickMous\Binsta\Internals\Requests;

class Request
{
    public function __construct()
    {
        foreach ($_GET as $key => $value) {
            $this->{$key} = $value;
        }

        foreach ($_POST as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
