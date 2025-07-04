<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Response\Response;

class TestController extends BaseController
{
    public function index(): Response
    {
        return new Response("This is a test response from the TestController.");
    }
}
