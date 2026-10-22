<?php

namespace App\Providers;

use App\Http\Middleware\AdminMiddleware;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        config(['app.local' => 'id']);
        Carbon::setLocale('id');

        // Terapkan juga ke request update Livewire (wire:click, wire:submit) di halaman admin
        Livewire::addPersistentMiddleware([AdminMiddleware::class]);
    }
}
