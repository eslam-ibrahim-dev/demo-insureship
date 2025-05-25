<?php

namespace App\Providers;

use App\Services\MailerService;
use Illuminate\Support\ServiceProvider;

class MailerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(MailerService::class, function ($app) {
            return new MailerService($app['request']);
        });
    }
}