<?php

namespace NickMous\Binsta\Tests\Unit\Attributes;

use NickMous\Binsta\Internals\Attributes\Singleton;
use ReflectionClass;

covers(Singleton::class);

describe('Singleton', function (): void {
    test('creates singleton attribute', function (): void {
        $singleton = new Singleton();

        expect($singleton)->toBeInstanceOf(Singleton::class);
    });

    test('can be used as class attribute', function (): void {
        $reflection = new ReflectionClass(TestSingletonClass::class);
        $attributes = $reflection->getAttributes(Singleton::class);

        expect($attributes)->toHaveCount(1);
        expect($attributes[0]->getName())->toBe(Singleton::class);
    });
});

#[Singleton]
class TestSingletonClass
{
    public function test(): string
    {
        return 'test';
    }
}
