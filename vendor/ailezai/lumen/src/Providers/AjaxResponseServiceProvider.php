<?php

namespace AiLeZai\Lumen\Framework\Providers;

use AiLeZai\Lumen\Framework\Supports\AjaxResponse\AjaxResponse;
use Illuminate\Support\ServiceProvider;

class AjaxResponseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AjaxResponse::class, AjaxResponse::class);
    }
}