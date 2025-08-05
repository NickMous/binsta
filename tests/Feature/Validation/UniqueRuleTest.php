<?php

use NickMous\Binsta\Internals\Validation\Validators\UniqueRule;
use NickMous\Binsta\Managers\DatabaseManager;
use NickMous\Binsta\Kernel;

covers(UniqueRule::class);

describe('UniqueRule Feature Tests', function (): void {
    beforeEach(function (): void {
        // Initialize database for each test
        new Kernel()->init();
        DatabaseManager::instantiate();
    });

    afterEach(function (): void {
        // Clean up after each test
        \RedBeanPHP\R::nuke();
        \RedBeanPHP\R::close();
        DatabaseManager::reset();
    });

    test('validates unique values correctly with database', function (): void {
        $rule = new UniqueRule();

        // Set up parameters for user table, email field
        $rule->setParameters(['user', 'email']);

        // Test with non-existent email (should be valid/unique)
        expect($rule->validate('nonexistent@example.com'))->toBe(true);
    });

    test('validates with different table and field combinations', function (): void {
        $rule = new UniqueRule();

        // Test with different table/field combination
        $rule->setParameters(['post', 'slug']);
        expect($rule->validate('some-unique-slug'))->toBe(true);

        // Test whitespace trimming
        $rule->setParameters(['user', 'email']);
        expect($rule->validate('  test@example.com  '))->toBe(true);
    });

    test('handles multiple parameters correctly', function (): void {
        $rule = new UniqueRule();

        // Should use first two parameters only
        $rule->setParameters(['user', 'email', 'extra', 'params']);

        expect($rule->validate('unique@test.com'))->toBe(true);
    });

    test('detects existing values in database', function (): void {
        // Create a test user in the database
        $user = \RedBeanPHP\R::dispense('user');
        $user->email = 'existing@example.com';
        $user->name = 'Existing User';
        \RedBeanPHP\R::store($user);

        $rule = new UniqueRule();
        $rule->setParameters(['user', 'email']);

        // Should fail validation for existing email
        expect($rule->validate('existing@example.com'))->toBe(false);

        // Should pass validation for new email
        expect($rule->validate('new@example.com'))->toBe(true);

        // Database queries are typically case-insensitive for emails
        expect($rule->validate('EXISTING@EXAMPLE.COM'))->toBe(false);
    });
});