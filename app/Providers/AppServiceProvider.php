<?php

namespace App\Providers;

use App\Http\Middleware\AdminMiddleware;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        Carbon::setLocale('id');

        Blade::directive('rupiah', fn ($nilai) => "<?php echo e(\\App\\Support\\Format::rupiah($nilai)); ?>");
        Blade::directive('tanggal', fn ($waktu) => "<?php echo e(\\App\\Support\\Format::tanggal($waktu)); ?>");

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Terapkan juga ke request update Livewire (wire:click, wire:submit) di halaman admin
        Livewire::addPersistentMiddleware([AdminMiddleware::class]);
    }
}
