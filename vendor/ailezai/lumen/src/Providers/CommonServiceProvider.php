<?php

namespace AiLeZai\Lumen\Framework\Providers;

use Illuminate\Support\ServiceProvider;
use AiLeZai\Lumen\Framework\Console\Commands\InitCommand;

class CommonServiceProvider extends ServiceProvider
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
    }
}