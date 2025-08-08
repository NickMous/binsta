<?php

use NickMous\Binsta\Entities\User;
use NickMous\Binsta\Internals\Services\ControllerService;
use NickMous\Binsta\Kernel;
use NickMous\Binsta\Repositories\UserRepository;

describe('Entity Parameter Resolution', function (): void {
    beforeEach(function (): void {
        // Initialize the kernel for database connection
        new Kernel()->init();

        // Clear any existing test data
        \RedBeanPHP\R::wipe('user');

        // Create a test user for entity resolution testing
        $userRepository = new UserRepository();
        $this->testUser = $userRepository->create('Test User', 'testuser', 'test@example.com', 'password123');
    });

    afterEach(function (): void {
        // Clean up after each test
        \RedBeanPHP\R::wipe('user');
    });

    test('automatically resolves User entity from route parameter', function (): void {
        // Create controller service with actual routes
        $controllerService = new ControllerService(__DIR__ . '/../Datasets/entity-test-routes.php');

        // Test the route with entity parameter resolution
        $userId = $this->testUser->getId();

        ob_start();
        $controllerService->callRoute("/api/users/{$userId}", 'GET');
        $output = ob_get_clean();

        // Verify the response contains the user data
        $responseData = json_decode($output, true);

        expect($responseData)->toBeArray()
            ->and($responseData)->toHaveKey('id')
            ->and($responseData)->toHaveKey('name')
            ->and($responseData)->toHaveKey('email')
            ->and($responseData['id'])->toBe($userId)
            ->and($responseData['name'])->toBe('Test User')
            ->and($responseData['email'])->toBe('test@example.com');
    });

    test('throws exception when entity not found for route parameter', function (): void {
        // Create controller service with actual routes
        $controllerService = new ControllerService(__DIR__ . '/../Datasets/entity-test-routes.php');

        // Test with non-existent user ID - expect exception to be thrown
        expect(function () use ($controllerService): void {
            $controllerService->callRoute('/api/users/999999', 'GET');
        })->toThrow(\InvalidArgumentException::class, 'User not found for parameter: 999999');
    });
});
