<?php

use NickMous\Binsta\Internals\Requests\Request;

covers(Request::class);

describe('Request', function (): void {
    test('creates request with GET parameters', function (): void {
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

    test('creates request with POST parameters', function (): void {
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

    test('creates request with both GET and POST parameters', function (): void {
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

    test('creates request with no parameters', function (): void {
        // Ensure no parameters are set
        $originalGet = $_GET;
        $originalPost = $_POST;
        $_GET = [];
        $_POST = [];

        $request = new Request();

        // Should not have any parameters
        expect($request->all())->toBe([]);

        // Restore original values
        $_GET = $originalGet;
        $_POST = $originalPost;
    });

    test('POST parameters take precedence over GET parameters', function (): void {
        // Set up conflicting GET and POST parameters
        $_GET['name'] = 'get-value';
        $_POST['name'] = 'post-value';

        $request = new Request();

        expect($request->name)->toBe('post-value'); // POST should win
        expect($request->get('name'))->toBe('post-value');

        // Clean up
        unset($_GET['name']);
        unset($_POST['name']);
    });

    test('provides convenient methods for parameter access', function (): void {
        $_GET['search'] = 'test';
        $_POST['action'] = 'submit';

        $request = new Request();

        // Test get() method with default values
        expect($request->get('search'))->toBe('test');
        expect($request->get('missing', 'default'))->toBe('default');

        // Test has() method
        expect($request->has('search'))->toBe(true);
        expect($request->has('missing'))->toBe(false);

        // Test isset() magic method
        expect(isset($request->search))->toBe(true);
        expect(isset($request->missing))->toBe(false);

        // Test all() method
        expect($request->all())->toBe([
            'search' => 'test',
            'action' => 'submit'
        ]);

        // Clean up
        unset($_GET['search']);
        unset($_POST['action']);
    });

    test('allows setting parameters dynamically', function (): void {
        $request = new Request();

        // Test __set magic method
        $request->dynamic = 'value';
        expect($request->dynamic)->toBe('value')
            ->and($request->get('dynamic'))->toBe('value')
            ->and($request->has('dynamic'))->toBe(true)
            ->and(isset($request->dynamic))->toBe(true);
    });
});
