<?php

use NickMous\Binsta\Internals\Validation\Validators\SameRule;

covers(SameRule::class);

describe('SameRule', function (): void {
    test('getKey returns correct key', function (): void {
        $rule = new SameRule();
        expect($rule->getKey())->toBe('same');
    });

    test('validates that values match', function (): void {
        $rule = new SameRule();
        $rule->setParameters(['password']);
        $rule->setContext([
            'password' => 'secret123',
            'email' => 'test@example.com'
        ]);

        expect($rule->validate('secret123'))->toBe(true);   // matches password
        expect($rule->validate('different'))->toBe(false);  // doesn't match
        expect($rule->validate(''))->toBe(false);           // empty doesn't match
    });

    test('validates with different field names', function (): void {
        $rule = new SameRule();

        // Test comparing to email field
        $rule->setParameters(['email']);
        $rule->setContext([
            'email' => 'test@example.com',
            'password' => 'secret123'
        ]);

        expect($rule->validate('test@example.com'))->toBe(true);
        expect($rule->validate('other@example.com'))->toBe(false);
    });

    test('fails when comparison field does not exist in context', function (): void {
        $rule = new SameRule();
        $rule->setParameters(['missing_field']);
        $rule->setContext([
            'password' => 'secret123'
        ]);

        expect($rule->validate('secret123'))->toBe(false);  // field doesn't exist
        expect($rule->validate('anything'))->toBe(false);   // field doesn't exist
    });

    test('performs strict comparison', function (): void {
        $rule = new SameRule();
        $rule->setParameters(['number']);
        $rule->setContext([
            'number' => '123'  // string
        ]);

        expect($rule->validate('123'))->toBe(true);   // string matches string
        expect($rule->validate(123))->toBe(false);    // int doesn't match string (strict)
        expect($rule->validate('0123'))->toBe(false); // different string doesn't match
    });

    test('handles null and empty values correctly', function (): void {
        $rule = new SameRule();
        $rule->setParameters(['nullable_field']);
        $rule->setContext([
            'nullable_field' => null,
            'empty_field' => ''
        ]);

        expect($rule->validate(null))->toBe(true);    // null matches null
        expect($rule->validate(''))->toBe(false);     // empty string doesn't match null

        // Test with empty string field
        $rule->setParameters(['empty_field']);
        expect($rule->validate(''))->toBe(true);      // empty matches empty
        expect($rule->validate(null))->toBe(false);   // null doesn't match empty
    });

    test('defaults to empty field name when no parameters', function (): void {
        $rule = new SameRule();
        $rule->setParameters([]);  // no parameters
        $rule->setContext([
            'password' => 'secret123'
        ]);

        // Should try to compare to field with empty name, which doesn't exist
        expect($rule->validate('anything'))->toBe(false);
    });

    test('handles multiple parameters but only uses first', function (): void {
        $rule = new SameRule();
        $rule->setParameters(['password', 'email', 'username']);
        $rule->setContext([
            'password' => 'secret123',
            'email' => 'test@example.com',
            'username' => 'john'
        ]);

        // Should only compare to first parameter (password)
        expect($rule->validate('secret123'))->toBe(true);    // matches password
        expect($rule->validate('test@example.com'))->toBe(false); // doesn't match password
        expect($rule->validate('john'))->toBe(false);        // doesn't match password
    });

    test('handles boolean values correctly', function (): void {
        $rule = new SameRule();
        $rule->setParameters(['is_admin']);
        $rule->setContext([
            'is_admin' => true
        ]);

        expect($rule->validate(true))->toBe(true);
        expect($rule->validate(false))->toBe(false);
        expect($rule->validate(1))->toBe(false);     // strict comparison
        expect($rule->validate('true'))->toBe(false); // strict comparison
    });

    test('updates context when setContext is called multiple times', function (): void {
        $rule = new SameRule();
        $rule->setParameters(['password']);

        // Set initial context
        $rule->setContext(['password' => 'original']);
        expect($rule->validate('original'))->toBe(true);

        // Update context
        $rule->setContext(['password' => 'updated']);
        expect($rule->validate('original'))->toBe(false);
        expect($rule->validate('updated'))->toBe(true);
    });
});
