<?php

use NickMous\Binsta\Internals\Attributes\Priority;

covers(Priority::class);

describe('Priority', function (): void {
    test('creates priority with value', function (): void {
        $priority = new Priority(5);

        expect($priority->value)->toBe(5);
    });

    test('creates priority with different value', function (): void {
        $priority = new Priority(1);

        expect($priority->value)->toBe(1);
    });
});
