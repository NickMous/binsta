<?php

use NickMous\Binsta\Internals\Requests\Request;

covers(Request::class);

describe('Request', function () {
    test('creates request with GET parameters', function () {
        // Set up GET parameters
        $_GET['test'] = 'value';
        $_GET['another'] = 'param';

        $request = new Request();

        expect($request->test)->toBe('value');
        expect($request->another)->toBe('param');

        // Clean up
        unset($_GET['test']);
        unset($_GET['another']);
    });

    test('creates request with POST parameters', function () {
        // Set up POST parameters
        $_POST['username'] = 'testuser';
        $_POST['password'] = 'testpass';

        $request = new Request();

        expect($request->username)->toBe('testuser');
        expect($request->password)->toBe('testpass');

        // Clean up
        unset($_POST['username']);
        unset($_POST['password']);
    });

    test('creates request with both GET and POST parameters', function () {
        // Set up both GET and POST parameters
        $_GET['page'] = '1';
        $_POST['action'] = 'save';

        $request = new Request();

        expect($request->page)->toBe('1');
        expect($request->action)->toBe('save');

        // Clean up
        unset($_GET['page']);
        unset($_POST['action']);
    });

    test('creates request with no parameters', function () {
        // Ensure no parameters are set
        $originalGet = $_GET;
        $originalPost = $_POST;
        $_GET = [];
        $_POST = [];

        $request = new Request();

        // Should not have any properties set
        expect(get_object_vars($request))->toBe([]);

        // Restore original values
        $_GET = $originalGet;
        $_POST = $originalPost;
    });
});
