<?php

use NickMous\Binsta\Internals\Validation\Validators\StringRule;

covers(StringRule::class);

describe('StringRule', function (): void {
    test('getKey returns correct key', function (): void {
        $rule = new StringRule();
        expect($rule->getKey())->toBe('string');
    });

    test('validates non-empty strings as valid', function (): void {
        $rule = new StringRule();
        
        expect($rule->validate('test'))->toBe(true);
        expect($rule->validate('hello world'))->toBe(true);
        expect($rule->validate('123'))->toBe(true);              // numeric string
        expect($rule->validate('0'))->toBe(true);                // string zero
        expect($rule->validate('true'))->toBe(true);             // string boolean
        expect($rule->validate('special!@#$%^&*()chars'))->toBe(true);
    });

    test('validates strings with whitespace', function (): void {
        $rule = new StringRule();
        
        expect($rule->validate('  test  '))->toBe(true);         // whitespace around content
        expect($rule->validate('   '))->toBe(false);             // whitespace only (trimmed to empty)
        expect($rule->validate("\t\n\r"))->toBe(false);          // only whitespace chars
        expect($rule->validate("\t hello \n"))->toBe(true);      // whitespace around content
    });

    test('rejects empty string', function (): void {
        $rule = new StringRule();
        
        expect($rule->validate(''))->toBe(false);                // empty string
    });

    test('rejects non-string values', function (): void {
        $rule = new StringRule();
        
        expect($rule->validate(null))->toBe(false);              // null
        expect($rule->validate(123))->toBe(false);               // integer
        expect($rule->validate(12.34))->toBe(false);             // float
        expect($rule->validate(true))->toBe(false);              // boolean true
        expect($rule->validate(false))->toBe(false);             // boolean false
        expect($rule->validate([]))->toBe(false);                // empty array
        expect($rule->validate(['test']))->toBe(false);          // array with content
        expect($rule->validate(new stdClass()))->toBe(false);    // object
    });

    test('trims whitespace before checking emptiness', function (): void {
        $rule = new StringRule();
        
        // The rule trims the string, so these should fail
        expect($rule->validate(' '))->toBe(false);               // single space
        expect($rule->validate('  '))->toBe(false);              // multiple spaces
        expect($rule->validate("\t"))->toBe(false);              // tab
        expect($rule->validate("\n"))->toBe(false);              // newline
        expect($rule->validate("\r"))->toBe(false);              // carriage return
        
        // But these should pass because they have content after trimming
        expect($rule->validate(' a '))->toBe(true);              // space around letter
        expect($rule->validate("\ta\t"))->toBe(true);            // tab around letter
    });
});