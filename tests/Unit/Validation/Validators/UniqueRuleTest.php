<?php

use NickMous\Binsta\Internals\Validation\Validators\UniqueRule;
use NickMous\Binsta\Managers\DatabaseManager;
use NickMous\Binsta\Kernel;

covers(UniqueRule::class);

describe('UniqueRule', function (): void {
    test('getKey returns correct key', function (): void {
        $rule = new UniqueRule();
        expect($rule->getKey())->toBe('unique');
    });

    test('validates unique values correctly', function (): void {
        $rule = new UniqueRule();

        // Set up parameters for user table, email field
        $rule->setParameters(['user', 'email']);

        // Test with non-existent email (should be valid/unique)
        expect($rule->validate('nonexistent@example.com'))->toBe(true);

        // Test with empty string (should be invalid)
        expect($rule->validate(''))->toBe(false);

        // Test with null (should be invalid)
        expect($rule->validate(null))->toBe(false);

        // Test with non-string (should be invalid)
        expect($rule->validate(123))->toBe(false);
    });

    test('fails validation when parameters are missing', function (): void {
        $rule = new UniqueRule();

        // No parameters set
        expect($rule->validate('test@example.com'))->toBe(false);

        // Only table parameter
        $rule->setParameters(['user']);
        expect($rule->validate('test@example.com'))->toBe(false);

        // Empty parameters
        $rule->setParameters([]);
        expect($rule->validate('test@example.com'))->toBe(false);
    });

    test('validates with different table and field combinations', function (): void {
        $rule = new UniqueRule();

        // Test with different table/field combination
        $rule->setParameters(['post', 'slug']);
        expect($rule->validate('some-unique-slug'))->toBe(true);

        // Test whitespace trimming
        $rule->setParameters(['user', 'email']);
        expect($rule->validate('  test@example.com  '))->toBe(true);
        expect($rule->validate('   '))->toBe(false); // Only whitespace
    });

    test('handles multiple parameters correctly', function (): void {
        $rule = new UniqueRule();

        // Should use first two parameters only
        $rule->setParameters(['user', 'email', 'extra', 'params']);

        expect($rule->validate('unique@test.com'))->toBe(true);
    });

    test('detects existing values in database', function (): void {
        // Initialize database for this specific test
        new Kernel()->init();
        DatabaseManager::instantiate();

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

        // Clean up
        \RedBeanPHP\R::nuke();
        \RedBeanPHP\R::close();
        DatabaseManager::reset();
    });
});
