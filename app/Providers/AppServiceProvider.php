<?php

namespace App\Providers;

use App\Http\Middleware\AdminMiddleware;
use App\Pembayaran\MidtransGateway;
use App\Pembayaran\PaymentGateway;
use App\Pembayaran\SimulasiGateway;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function () {
            if (config('pembayaran.driver') === 'simulasi') {
                // simulasi bisa menandai pesanan dibayar tanpa uang masuk
                if ($this->app->isProduction()) {
                    throw new RuntimeException('PEMBAYARAN_DRIVER=simulasi tidak boleh dipakai di production.');
                }

                return new SimulasiGateway;
            }

            return new MidtransGateway(
                (string) config('pembayaran.midtrans.server_key'),
                (string) config('pembayaran.midtrans.client_key'),
                config('pembayaran.midtrans.produksi'),
            );
        });
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
