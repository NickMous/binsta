<?php

use NickMous\Binsta\Internals\Validation\Validators\MinRule;

covers(MinRule::class);

describe('MinRule', function (): void {
    test('getKey returns correct key', function (): void {
        $rule = new MinRule();
        expect($rule->getKey())->toBe('min');
    });

    test('validates strings with minimum length', function (): void {
        $rule = new MinRule();
        $rule->setParameters(['5']);

        expect($rule->validate('hello'))->toBe(true);  // exactly 5 chars
        expect($rule->validate('hello world'))->toBe(true);  // more than 5 chars
        expect($rule->validate('hi'))->toBe(false);  // less than 5 chars
        expect($rule->validate(''))->toBe(false);  // empty string
    });

    test('validates with different minimum lengths', function (): void {
        $rule = new MinRule();

        // Test with min length 1
        $rule->setParameters(['1']);
        expect($rule->validate('a'))->toBe(true);
        expect($rule->validate(''))->toBe(false);

        // Test with min length 10
        $rule->setParameters(['10']);
        expect($rule->validate('1234567890'))->toBe(true);  // exactly 10
        expect($rule->validate('12345678901'))->toBe(true);  // more than 10
        expect($rule->validate('123456789'))->toBe(false);   // less than 10
    });

    test('defaults to 0 when no parameters are set', function (): void {
        $rule = new MinRule();
        
        expect($rule->validate(''))->toBe(true);      // empty string passes with min 0
        expect($rule->validate('any'))->toBe(true);   // any string passes with min 0
    });

    test('handles invalid parameter gracefully', function (): void {
        $rule = new MinRule();
        $rule->setParameters([]);  // empty parameters array

        expect($rule->validate('test'))->toBe(true);  // should default to 0
        expect($rule->validate(''))->toBe(true);      // empty passes with default 0
    });

    test('only validates strings', function (): void {
        $rule = new MinRule();
        $rule->setParameters(['5']);

        expect($rule->validate(null))->toBe(false);
        expect($rule->validate(123))->toBe(false);
        expect($rule->validate(true))->toBe(false);
        expect($rule->validate([]))->toBe(false);
        expect($rule->validate(new stdClass()))->toBe(false);
    });

    test('converts parameter to integer', function (): void {
        $rule = new MinRule();
        $rule->setParameters(['5.7']);  // float as string

        expect($rule->validate('hello'))->toBe(true);   // 5 chars, should pass with int(5.7) = 5
        expect($rule->validate('hi'))->toBe(false);     // 2 chars, should fail
    });

    test('handles multiple parameters but only uses first', function (): void {
        $rule = new MinRule();
        $rule->setParameters(['3', '10', '20']);  // multiple parameters

        expect($rule->validate('abc'))->toBe(true);   // exactly 3 chars (first param)
        expect($rule->validate('ab'))->toBe(false);   // less than 3 chars
    });
});