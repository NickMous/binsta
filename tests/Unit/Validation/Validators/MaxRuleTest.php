<?php

use NickMous\Binsta\Internals\Validation\Validators\MaxRule;

covers(MaxRule::class);

describe('MaxRule', function (): void {
    test('getKey returns correct key', function (): void {
        $rule = new MaxRule();
        expect($rule->getKey())->toBe('max');
    });

    test('validates strings with maximum length', function (): void {
        $rule = new MaxRule();
        $rule->setParameters(['5']);

        expect($rule->validate('hello'))->toBe(true);  // exactly 5 chars
        expect($rule->validate('hi'))->toBe(true);  // less than 5 chars
        expect($rule->validate(''))->toBe(true);  // empty string
        expect($rule->validate('hello world'))->toBe(false);  // more than 5 chars
    });

    test('validates with different maximum lengths', function (): void {
        $rule = new MaxRule();

        // Test with max length 1
        $rule->setParameters(['1']);
        expect($rule->validate('a'))->toBe(true);
        expect($rule->validate(''))->toBe(true);
        expect($rule->validate('ab'))->toBe(false);

        // Test with max length 10
        $rule->setParameters(['10']);
        expect($rule->validate('1234567890'))->toBe(true);  // exactly 10
        expect($rule->validate('123456789'))->toBe(true);   // less than 10
        expect($rule->validate('12345678901'))->toBe(false);  // more than 10
    });

    test('defaults to 0 when no parameters are set', function (): void {
        $rule = new MaxRule();

        expect($rule->validate(''))->toBe(true);      // empty string passes with max 0
        expect($rule->validate('any'))->toBe(false);   // any non-empty string fails with max 0
    });

    test('handles invalid parameter gracefully', function (): void {
        $rule = new MaxRule();
        $rule->setParameters([]);  // empty parameters array

        expect($rule->validate(''))->toBe(true);      // empty passes with default 0
        expect($rule->validate('test'))->toBe(false);  // non-empty fails with default 0
    });

    test('only validates strings', function (): void {
        $rule = new MaxRule();
        $rule->setParameters(['5']);

        expect($rule->validate(null))->toBe(false);
        expect($rule->validate(123))->toBe(false);
        expect($rule->validate(true))->toBe(false);
        expect($rule->validate([]))->toBe(false);
        expect($rule->validate(new stdClass()))->toBe(false);
    });

    test('converts parameter to integer', function (): void {
        $rule = new MaxRule();
        $rule->setParameters(['5.7']);  // float as string

        expect($rule->validate('hello'))->toBe(true);   // 5 chars, should pass with int(5.7) = 5
        expect($rule->validate('hello!'))->toBe(false);  // 6 chars, should fail
    });

    test('handles multiple parameters but only uses first', function (): void {
        $rule = new MaxRule();
        $rule->setParameters(['3', '10', '20']);  // multiple parameters

        expect($rule->validate('abc'))->toBe(true);   // exactly 3 chars (first param)
        expect($rule->validate('ab'))->toBe(true);    // less than 3 chars
        expect($rule->validate('abcd'))->toBe(false); // more than 3 chars
    });

    test('validates boundary conditions', function (): void {
        $rule = new MaxRule();
        $rule->setParameters(['20']);

        // Test exactly at boundary
        expect($rule->validate('12345678901234567890'))->toBe(true);  // exactly 20 chars

        // Test one over boundary
        expect($rule->validate('123456789012345678901'))->toBe(false); // 21 chars

        // Test well under boundary
        expect($rule->validate('short'))->toBe(true); // 5 chars
    });
});
