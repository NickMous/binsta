<?php

use NickMous\Binsta\Internals\Validation\Validators\RequiredRule;

covers(RequiredRule::class);

describe('RequiredRule', function (): void {
    test('getKey returns correct key', function (): void {
        $rule = new RequiredRule();
        expect($rule->getKey())->toBe('required');
    });

    test('validates non-empty strings as valid', function (): void {
        $rule = new RequiredRule();
        
        expect($rule->validate('test'))->toBe(true);
        expect($rule->validate('hello world'))->toBe(true);
        expect($rule->validate('0'))->toBe(true);        // string zero is valid
        expect($rule->validate(' '))->toBe(false);       // space is invalid (trimmed to empty)
    });

    test('validates empty and null values as invalid', function (): void {
        $rule = new RequiredRule();
        
        expect($rule->validate(''))->toBe(false);        // empty string
        expect($rule->validate('   '))->toBe(false);     // whitespace only (trimmed to empty)
        expect($rule->validate(null))->toBe(false);      // null
    });

    test('validates non-string values', function (): void {
        $rule = new RequiredRule();
        
        expect($rule->validate(0))->toBe(true);          // number zero is valid
        expect($rule->validate(123))->toBe(true);        // positive number
        expect($rule->validate(-123))->toBe(true);       // negative number
        expect($rule->validate(0.0))->toBe(true);        // float zero
        expect($rule->validate(false))->toBe(true);      // boolean false is valid
        expect($rule->validate(true))->toBe(true);       // boolean true is valid
        expect($rule->validate([]))->toBe(true);         // empty array is valid
        expect($rule->validate(['item']))->toBe(true);   // non-empty array is valid
        expect($rule->validate(new stdClass()))->toBe(true); // object is valid
    });

    test('trims whitespace from strings before validation', function (): void {
        $rule = new RequiredRule();
        
        expect($rule->validate('  test  '))->toBe(true);    // whitespace around content is valid
        expect($rule->validate("\t\n\r"))->toBe(false);     // only whitespace chars is invalid
        expect($rule->validate("\t hello \n"))->toBe(true); // whitespace around content is valid
    });
});