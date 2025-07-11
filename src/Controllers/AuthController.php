<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Requests\Request;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Internals\Response\Response;

class AuthController extends BaseController
{
    public function login(Request $request): Response
    {
        return new JsonResponse([]) ;
    }
}
