<?php

use NickMous\Binsta\Internals\Response\VueResponse;

covers(VueResponse::class);

describe('VueResponse', function (): void {
    test('creates vue response with default values', function (): void {
        $response = new VueResponse();

        expect($response->componentName)->toBe("");
        expect($response->content)->toBe("");
        expect($response->statusCode)->toBe(200);
        expect($response->headers)->toBe([]);
        expect($response->props)->toBe([]);
    });

    test('creates vue response with component name', function (): void {
        $response = new VueResponse('HomePage');

        expect($response->componentName)->toBe('home-page');
        expect($response->statusCode)->toBe(200);
    });

    test('converts component name from PascalCase to kebab-case', function (): void {
        $response = new VueResponse('UserProfilePage');

        expect($response->componentName)->toBe('user-profile-page');
    });

    test('handles single word component names', function (): void {
        $response = new VueResponse('Dashboard');

        expect($response->componentName)->toBe('dashboard');
    });

    test('handles already lowercase component names', function (): void {
        $response = new VueResponse('login');

        expect($response->componentName)->toBe('login');
    });

    test('creates vue response with all parameters', function (): void {
        $headers = ['X-Component' => 'vue'];
        $response = new VueResponse('UserSettings', statusCode: 201, headers: $headers);

        expect($response->componentName)->toBe('user-settings');
        expect($response->statusCode)->toBe(201);
        expect($response->headers)->toBe($headers);
    });

    test('component name can be modified after creation', function (): void {
        $response = new VueResponse('HomePage');

        expect($response->componentName)->toBe('home-page');

        $response->componentName = 'AboutPage';
        expect($response->componentName)->toBe('about-page');
    });

    test('handles complex component names with multiple words', function (): void {
        $response = new VueResponse('AdminUserManagementDashboard');

        expect($response->componentName)->toBe('admin-user-management-dashboard');
    });

    test('handles component names with consecutive uppercase letters', function (): void {
        $response = new VueResponse('XMLHttpRequest');

        expect($response->componentName)->toBe('x-m-l-http-request');
    });

    test('handles empty component name', function (): void {
        $response = new VueResponse('');

        expect($response->componentName)->toBe('');
    });

    test('handles component name with numbers', function (): void {
        $response = new VueResponse('Page404Error');

        expect($response->componentName)->toBe('page404-error');
    });

    test('creates vue response with props', function (): void {
        $props = ['user' => '1', 'title' => 'Dashboard'];
        $response = new VueResponse('Dashboard', props: $props);

        expect($response->componentName)->toBe('dashboard');
        expect($response->props)->toBe($props);
        expect($response->statusCode)->toBe(200);
    });

    test('creates vue response with component name, props, status code and headers', function (): void {
        $props = ['user' => '2', 'role' => 'admin'];
        $headers = ['X-Component' => 'admin'];
        $response = new VueResponse('AdminPanel', props: $props, statusCode: 201, headers: $headers);

        expect($response->componentName)->toBe('admin-panel');
        expect($response->props)->toBe($props);
        expect($response->statusCode)->toBe(201);
        expect($response->headers)->toBe($headers);
    });
});
