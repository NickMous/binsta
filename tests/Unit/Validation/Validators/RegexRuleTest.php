<?php

use NickMous\Binsta\Internals\Validation\Validators\RegexRule;

covers(RegexRule::class);

describe('RegexRule', function (): void {
    test('getKey returns correct key', function (): void {
        $rule = new RegexRule();
        expect($rule->getKey())->toBe('regex');
    });

    test('validates strings against regex pattern', function (): void {
        $rule = new RegexRule();
        $rule->setParameters(['/^[a-zA-Z0-9_]+$/']); // alphanumeric and underscore pattern

        expect($rule->validate('user123'))->toBe(true);
        expect($rule->validate('test_user'))->toBe(true);
        expect($rule->validate('User_Name_123'))->toBe(true);
        expect($rule->validate('USERNAME'))->toBe(true);

        expect($rule->validate('user-name'))->toBe(false);  // contains dash
        expect($rule->validate('user@name'))->toBe(false);  // contains @
        expect($rule->validate('user name'))->toBe(false);  // contains space
        expect($rule->validate('user!'))->toBe(false);      // contains !
    });

    test('validates with different regex patterns', function (): void {
        $rule = new RegexRule();

        // Test digits only
        $rule->setParameters(['/^\d+$/']);
        expect($rule->validate('123'))->toBe(true);
        expect($rule->validate('456789'))->toBe(true);
        expect($rule->validate('123abc'))->toBe(false);
        expect($rule->validate('abc'))->toBe(false);

        // Test email-like pattern
        $rule->setParameters(['/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/']);
        expect($rule->validate('test@example.com'))->toBe(true);
        expect($rule->validate('user.name+tag@domain.co.uk'))->toBe(true);
        expect($rule->validate('invalid-email'))->toBe(false);
        expect($rule->validate('test@'))->toBe(false);
    });

    test('fails validation with empty pattern', function (): void {
        $rule = new RegexRule();
        // No parameters set, pattern remains empty

        expect($rule->validate('anything'))->toBe(false);
        expect($rule->validate(''))->toBe(false);
    });

    test('handles invalid parameter gracefully', function (): void {
        $rule = new RegexRule();
        $rule->setParameters([]);  // empty parameters array

        expect($rule->validate('test'))->toBe(false);  // should fail with empty pattern
        expect($rule->validate(''))->toBe(false);      // should fail with empty pattern
    });

    test('only validates strings', function (): void {
        $rule = new RegexRule();
        $rule->setParameters(['/^[a-zA-Z0-9_]+$/']);

        expect($rule->validate(null))->toBe(false);
        expect($rule->validate(123))->toBe(false);
        expect($rule->validate(true))->toBe(false);
        expect($rule->validate([]))->toBe(false);
        expect($rule->validate(new stdClass()))->toBe(false);
    });

    test('handles multiple parameters but only uses first', function (): void {
        $rule = new RegexRule();
        $rule->setParameters(['/^\d+$/', '/^[a-z]+$/', '/^[A-Z]+$/']); // multiple patterns

        expect($rule->validate('123'))->toBe(true);   // matches first pattern (digits)
        expect($rule->validate('abc'))->toBe(false);  // doesn't match first pattern
        expect($rule->validate('ABC'))->toBe(false);  // doesn't match first pattern
    });

    test('validates username pattern specifically', function (): void {
        $rule = new RegexRule();
        $rule->setParameters(['/^[a-zA-Z0-9_]+$/']); // username pattern from RegisterRequest

        // Valid usernames
        expect($rule->validate('user'))->toBe(true);
        expect($rule->validate('user123'))->toBe(true);
        expect($rule->validate('test_user'))->toBe(true);
        expect($rule->validate('User_Name_123'))->toBe(true);
        expect($rule->validate('a'))->toBe(true);
        expect($rule->validate('USERNAME'))->toBe(true);
        expect($rule->validate('_underscore'))->toBe(true);
        expect($rule->validate('123numbers'))->toBe(true);

        // Invalid usernames
        expect($rule->validate('user-name'))->toBe(false);  // dash
        expect($rule->validate('user.name'))->toBe(false);  // dot
        expect($rule->validate('user name'))->toBe(false);  // space
        expect($rule->validate('user@name'))->toBe(false);  // @
        expect($rule->validate('user+name'))->toBe(false);  // +
        expect($rule->validate('user!'))->toBe(false);      // !
        expect($rule->validate('user#name'))->toBe(false);  // #
        expect($rule->validate('user$name'))->toBe(false);  // $
        expect($rule->validate(''))->toBe(false);           // empty string
    });

    test('validates complex patterns', function (): void {
        $rule = new RegexRule();

        // Date pattern YYYY-MM-DD
        $rule->setParameters(['/^\d{4}-\d{2}-\d{2}$/']);
        expect($rule->validate('2024-12-25'))->toBe(true);
        expect($rule->validate('2024-01-01'))->toBe(true);
        expect($rule->validate('24-12-25'))->toBe(false);
        expect($rule->validate('2024/12/25'))->toBe(false);
        expect($rule->validate('2024-1-1'))->toBe(false);

        // Phone number pattern
        $rule->setParameters(['/^\+?[1-9]\d{1,14}$/']);
        expect($rule->validate('1234567890'))->toBe(true);
        expect($rule->validate('+31612345678'))->toBe(true);
        expect($rule->validate('0612345678'))->toBe(false); // starts with 0
        expect($rule->validate('+abc123'))->toBe(false);    // contains letters
    });

    test('handles invalid regex patterns gracefully', function (): void {
        $rule = new RegexRule();
        $rule->setParameters(['[unclosed']); // invalid regex (missing delimiters)

        // Should return false when regex is invalid
        expect($rule->validate('test'))->toBe(false);
        expect($rule->validate(''))->toBe(false);
    });
});
