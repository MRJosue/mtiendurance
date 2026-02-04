<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Livewire\Components\CardMinimizable;

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

        config([
                'mail.default' => 'mailpit',  // o 'mailpit' si lo creaste
                'mail.mailers.smtp.host' => '127.0.0.1',
                'mail.mailers.smtp.port' => 1025,
                'mail.mailers.smtp.encryption' => null,
                'mail.mailers.smtp.username' => null,
                'mail.mailers.smtp.password' => null,
            ]);

        Blade::component('card-minimizable', CardMinimizable::class);
    }
}
