<?php

use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Managers\DatabaseManager;
use NickMous\Binsta\Kernel;

covers(BaseController::class);

it('retrieves a bean by ID', function (): void {
    // Initialize environment for database tests
    new Kernel()->init();

    DatabaseManager::instantiate();

    $bean = \RedBeanPHP\R::dispense('testbean');
    $bean->name = 'Test Bean';
    \RedBeanPHP\R::store($bean);

    $controller = new BaseController();
    $bean = $controller->getBeanById('testbean', 1);

    expect($bean)->toBeInstanceOf(\RedBeanPHP\OODBBean::class)
        ->and((int) $bean->id)->toBe(1);

    \RedBeanPHP\R::nuke();
    \RedBeanPHP\R::close();
    DatabaseManager::reset();
});
