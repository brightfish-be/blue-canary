<?php

use Laravel\Lumen\Routing\Router;

/** @var string $version */
app('router')->group(['prefix' => $version], function (Router $router) {

    $appRe = '[a-z0-9-]{36}';
    $ctrRe = '[a-z0-9\-_.]{6,255}';

    $router->addRoute(['GET', 'POST'], "event/{appUuid:$appRe}/{counter:$ctrRe}", 'EventController@store');

    $router->get('event/health', 'Controller@health');
});
