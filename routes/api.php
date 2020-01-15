<?php

use Laravel\Lumen\Routing\Router;

app('router')->group(['prefix' => 'v1'], function (Router $router) {
    $appRe = config('canary/settings.app.uuid_validation');
    $ctrRe = config('canary/settings.counter.name_validation');

    # Make case-insensitive
    $appRe = "(?i)$appRe(?-i)";
    $ctrRe = "(?i)$ctrRe(?-i)";

    $router->addRoute(['GET', 'POST'], "event/{appUuid:$appRe}/{counter:$ctrRe}", 'EventController@store');

    $router->get('event/health', 'Controller@health');
});
