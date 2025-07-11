<?php

use NickMous\Binsta\Internals\Attributes\Priority;

covers(Priority::class);

describe('Priority', function () {
    test('creates priority with value', function () {
        $priority = new Priority(5);

        expect($priority->value)->toBe(5);
    });

    test('creates priority with different value', function () {
        $priority = new Priority(1);

        expect($priority->value)->toBe(1);
    });
});
