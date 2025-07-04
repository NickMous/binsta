<?php

use Nickmous\MyOwnFramework\Kernel;

test('Session is being initialized', function (): void {
    new Kernel()->initializeSession();
    expect(session_status())->toBe(PHP_SESSION_ACTIVE);
});
