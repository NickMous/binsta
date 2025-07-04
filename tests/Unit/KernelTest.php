<?php

use NickMous\MyOwnFramework\Kernel;

test('Session is being initialized', function () {
    new Kernel()->initializeSession();
    expect(session_status())->toBe(PHP_SESSION_ACTIVE);
});
