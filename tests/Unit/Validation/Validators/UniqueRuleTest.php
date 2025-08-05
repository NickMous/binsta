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

    test('validates input types correctly', function (): void {
        $rule = new UniqueRule();

        // Set up parameters for user table, email field
        $rule->setParameters(['user', 'email']);

        // Test with empty string (should be invalid)
        expect($rule->validate(''))->toBe(false);

        // Test with null (should be invalid)
        expect($rule->validate(null))->toBe(false);

        // Test with non-string (should be invalid)
        expect($rule->validate(123))->toBe(false);

        // Test with whitespace only (should be invalid)
        expect($rule->validate('   '))->toBe(false);
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

    test('validates identifier security - rejects unsafe table names', function (): void {
        $rule = new UniqueRule();

        // Test SQL injection attempts in table name
        expect(fn() => $rule->setParameters(['user; DROP TABLE users; --', 'email']))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn() => $rule->setParameters(['user`', 'email']))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn() => $rule->setParameters(['user-table', 'email']))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn() => $rule->setParameters(['1user', 'email']))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('validates identifier security - rejects unsafe field names', function (): void {
        $rule = new UniqueRule();

        // Test SQL injection attempts in field name
        expect(fn() => $rule->setParameters(['user', 'email; DROP TABLE users; --']))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn() => $rule->setParameters(['user', 'email`']))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn() => $rule->setParameters(['user', 'email-field']))
            ->toThrow(\InvalidArgumentException::class);

        expect(fn() => $rule->setParameters(['user', '1email']))
            ->toThrow(\InvalidArgumentException::class);
    });
});
