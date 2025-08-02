<?php

use NickMous\Binsta\Internals\Containers\ValidationContainer;
use NickMous\Binsta\Internals\Validation\Validators\RequiredRule;
use NickMous\Binsta\Internals\Validation\Validators\EmailRule;
use NickMous\Binsta\Internals\Validation\Validators\StringRule;
use NickMous\Binsta\Internals\Validation\Validators\MinRule;
use NickMous\Binsta\Internals\Validation\Validators\SameRule;

covers(ValidationContainer::class);

describe('ValidationContainer', function (): void {
    test('getInstance returns singleton instance', function (): void {
        $instance1 = ValidationContainer::getInstance();
        $instance2 = ValidationContainer::getInstance();

        expect($instance1)->toBeInstanceOf(ValidationContainer::class);
        expect($instance1)->toBe($instance2); // Same instance
    });

    test('loads all validation rules on initialization', function (): void {
        $container = ValidationContainer::getInstance();

        // Test that basic validators are loaded
        expect($container->getValidator('required'))->toBeInstanceOf(RequiredRule::class);
        expect($container->getValidator('email'))->toBeInstanceOf(EmailRule::class);
        expect($container->getValidator('string'))->toBeInstanceOf(StringRule::class);
        expect($container->getValidator('min'))->toBeInstanceOf(MinRule::class);
        expect($container->getValidator('same'))->toBeInstanceOf(SameRule::class);
    });

    test('getValidator throws exception for unknown rule', function (): void {
        $container = ValidationContainer::getInstance();

        expect(fn() => $container->getValidator('unknown_rule'))
            ->toThrow(InvalidArgumentException::class, "Validation rule 'unknown_rule' not found.");
    });

    test('createValidator returns simple validator without parameters', function (): void {
        $container = ValidationContainer::getInstance();

        $validator = $container->createValidator('required');
        expect($validator)->toBeInstanceOf(RequiredRule::class);

        // Test that it works
        expect($validator->validate('test'))->toBe(true);
        expect($validator->validate(''))->toBe(false);
    });

    test('createValidator returns parameterized validator with parameters', function (): void {
        $container = ValidationContainer::getInstance();

        $validator = $container->createValidator('min', ['5']);
        expect($validator)->toBeInstanceOf(MinRule::class);

        // Test that parameters were set correctly
        expect($validator->validate('hello'))->toBe(true);   // 5 chars
        expect($validator->validate('hi'))->toBe(false);     // 2 chars
    });

    test('createValidator returns context-aware validator with context', function (): void {
        $container = ValidationContainer::getInstance();

        $context = ['password' => 'secret123'];
        $validator = $container->createValidator('same', ['password'], $context);
        expect($validator)->toBeInstanceOf(SameRule::class);

        // Test that context was set correctly
        expect($validator->validate('secret123'))->toBe(true);
        expect($validator->validate('different'))->toBe(false);
    });

    test('createValidator handles both parameters and context for same validator', function (): void {
        $container = ValidationContainer::getInstance();

        $context = ['password' => 'secret123', 'email' => 'test@example.com'];
        $validator = $container->createValidator('same', ['password'], $context);
        expect($validator)->toBeInstanceOf(SameRule::class);

        // Test that both parameters and context work
        expect($validator->validate('secret123'))->toBe(true);  // matches password
        expect($validator->validate('test@example.com'))->toBe(false); // doesn't match password field
    });

    test('createValidator works with empty parameters and context', function (): void {
        $container = ValidationContainer::getInstance();

        $validator = $container->createValidator('min', [], []);
        expect($validator)->toBeInstanceOf(MinRule::class);

        // Should default to min length 0
        expect($validator->validate(''))->toBe(true);
        expect($validator->validate('any'))->toBe(true);
    });

    test('createValidator throws exception for unknown rule', function (): void {
        $container = ValidationContainer::getInstance();

        expect(fn() => $container->createValidator('unknown_rule', ['param']))
            ->toThrow(InvalidArgumentException::class, "Validation rule 'unknown_rule' not found.");
    });

    test('createValidator handles validator that only implements ParameterizedValidationRule', function (): void {
        $container = ValidationContainer::getInstance();

        // MinRule only implements ParameterizedValidationRule, not ContextAwareValidationRule
        $validator = $container->createValidator('min', ['3'], ['some' => 'context']);
        expect($validator)->toBeInstanceOf(MinRule::class);

        // Parameters should work
        expect($validator->validate('abc'))->toBe(true);
        expect($validator->validate('ab'))->toBe(false);
        
        // Context should be ignored (no error)
    });

    test('createValidator handles validator that implements neither interface', function (): void {
        $container = ValidationContainer::getInstance();

        // RequiredRule implements neither ParameterizedValidationRule nor ContextAwareValidationRule
        $validator = $container->createValidator('required', ['param'], ['context' => 'value']);
        expect($validator)->toBeInstanceOf(RequiredRule::class);

        // Should work normally, ignoring parameters and context
        expect($validator->validate('test'))->toBe(true);
        expect($validator->validate(''))->toBe(false);
    });

    test('createValidator creates new instance each time', function (): void {
        $container = ValidationContainer::getInstance();

        $validator1 = $container->createValidator('min', ['5']);
        $validator2 = $container->createValidator('min', ['10']);

        expect($validator1)->toBeInstanceOf(MinRule::class);
        expect($validator2)->toBeInstanceOf(MinRule::class);
        expect($validator1)->not->toBe($validator2); // Different instances

        // Test that they have different configurations
        expect($validator1->validate('hello'))->toBe(true);  // 5 chars passes min 5
        expect($validator2->validate('hello'))->toBe(false); // 5 chars fails min 10
    });

    test('getValidator returns singleton instances', function (): void {
        $container = ValidationContainer::getInstance();

        $validator1 = $container->getValidator('required');
        $validator2 = $container->getValidator('required');

        expect($validator1)->toBe($validator2); // Same instance
    });

    test('createValidator vs getValidator behavior difference', function (): void {
        $container = ValidationContainer::getInstance();

        // getValidator returns the same instance
        $singleton1 = $container->getValidator('min');
        $singleton2 = $container->getValidator('min');
        expect($singleton1)->toBe($singleton2);

        // createValidator returns new instances
        $instance1 = $container->createValidator('min', ['5']);
        $instance2 = $container->createValidator('min', ['10']);
        expect($instance1)->not->toBe($instance2);
        expect($instance1)->not->toBe($singleton1);
    });
});