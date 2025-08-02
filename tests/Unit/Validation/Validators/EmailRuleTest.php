<?php

use NickMous\Binsta\Internals\Validation\Validators\EmailRule;

covers(EmailRule::class);

describe('EmailRule', function (): void {
    test('getKey returns correct key', function (): void {
        $rule = new EmailRule();
        expect($rule->getKey())->toBe('email');
    });

    test('validates correct email addresses', function (): void {
        $rule = new EmailRule();
        
        expect($rule->validate('test@example.com'))->toBe(true);
        expect($rule->validate('user.name@domain.com'))->toBe(true);
        expect($rule->validate('user+tag@example.org'))->toBe(true);
        expect($rule->validate('user_name@example-domain.co.uk'))->toBe(true);
        expect($rule->validate('123@456.com'))->toBe(true);
        expect($rule->validate('test@localhost'))->toBe(false);
    });

    test('rejects invalid email addresses', function (): void {
        $rule = new EmailRule();
        
        expect($rule->validate('invalid-email'))->toBe(false);
        expect($rule->validate('@example.com'))->toBe(false);        // missing username
        expect($rule->validate('test@'))->toBe(false);               // missing domain
        expect($rule->validate('test..test@example.com'))->toBe(false); // double dots
        expect($rule->validate('test@example'))->toBe(false);        // missing TLD (depends on filter_var)
        expect($rule->validate('test@.com'))->toBe(false);           // missing domain name
        expect($rule->validate('test@example.'))->toBe(false);       // trailing dot
    });

    test('rejects empty and null values', function (): void {
        $rule = new EmailRule();
        
        expect($rule->validate(''))->toBe(false);                    // empty string
        expect($rule->validate('   '))->toBe(false);                 // whitespace only
        expect($rule->validate(null))->toBe(false);                  // null
    });

    test('rejects non-string values', function (): void {
        $rule = new EmailRule();
        
        expect($rule->validate(123))->toBe(false);                   // number
        expect($rule->validate(true))->toBe(false);                  // boolean
        expect($rule->validate([]))->toBe(false);                    // array
        expect($rule->validate(new stdClass()))->toBe(false);        // object
    });

    test('handles edge cases', function (): void {
        $rule = new EmailRule();
        
        expect($rule->validate('a@b.co'))->toBe(true);               // minimal valid email
        expect($rule->validate('very.long.email.address@very.long.domain.name.com'))->toBe(true); // long email
        expect($rule->validate('test@example.com '))->toBe(false);   // trailing space (trimmed, but space in email is invalid)
        expect($rule->validate(' test@example.com'))->toBe(false);   // leading space
    });
});