<?php

namespace App\Infrastructure\Providers;

use App\Console\Commands\IgorTestCommand;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->commands([
            IgorTestCommand::class,
        ]);
    }
}
