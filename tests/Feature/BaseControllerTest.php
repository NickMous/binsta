<?php

use Nickmous\MyOwnFramework\Controllers\BaseController;
use Nickmous\MyOwnFramework\Managers\DatabaseManager;

covers(BaseController::class);

it('retrieves a bean by ID', function (): void {
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
    \RedBeanPHP\R::$toolboxes = [];
});
