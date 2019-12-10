<?php

namespace App\Providers;

use App\Event;
use App\MetricFactory;
use Illuminate\Support\ServiceProvider;

/**
 * Core service providers.
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        $this->app->bind(Event::class, function () {
            return new Event($this->app['db'], new MetricFactory($this->app['db']));
        });
    }
}
