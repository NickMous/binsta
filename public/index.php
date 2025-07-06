<?php

use NickMous\Binsta\Internals\Services\ControllerService;
use NickMous\Binsta\Kernel;

require __DIR__ . '/../vendor/autoload.php';

new Kernel()->init();
new ControllerService(__DIR__ . '/../routes/web.php')
    ->callRoute('/' . ($_GET['internal_dnt_params'] ?? ''));

//$controller = 'recipe';
//$method = 'index';
//
//// Parse URL parameters
//if (!empty($_GET['params'])) {
//    $params = explode('/', trim($_GET['params'], '/'));
//    $controller = strtolower($params[0]);
//    $method = $params[1] ?? $method;
//
//    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//        $method .= 'Post';
//    }
//}
//
//$requiresId = in_array($method, ['show', 'edit', 'editPost', 'delete', 'deletePost'], true);
//
//$controllerClass = 'Nickmous\\Binsta\\Controllers\\' . ucfirst($controller) . 'Controller';
//$args = $requiresId ? [(int)($_GET['id'] ?? null)] : [];
//
//if (method_exists($controllerClass, $method)) {
//    new $controllerClass()->{$method}(...$args);
//} else {
//    error(404, 'Method not found');
//}
