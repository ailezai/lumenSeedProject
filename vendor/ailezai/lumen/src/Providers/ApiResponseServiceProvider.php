<?php

namespace AiLeZai\Lumen\Framework\Providers;

use AiLeZai\Lumen\Framework\Console\Commands\InitCommand;
use AiLeZai\Lumen\Framework\Supports\ApiResponse\ApiResponseFactory;
use AiLeZai\Lumen\Framework\Supports\ApiResponse\Json\JsonApiResponseFactory;
use Illuminate\Support\ServiceProvider;

class ApiResponseServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        InitCommand::class
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
        $this->app->singleton(ApiResponseFactory::class, JsonApiResponseFactory::class);
    }
}