<?php

namespace App\Support;

use App\Enums\StatusPesanan;
use App\Models\ItemPesanan;
use App\Models\Pesanan;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// Angka penjualan untuk dashboard admin. Omzet hanya dari pesanan yang sudah dibayar, pada hari pembayarannya.
class RingkasanPenjualan
{
    // ongkir diteruskan ke kurir, jadi omzet toko adalah subtotal sesudah diskon
    private const OMZET = 'subtotal - diskon';

    /** Omzet sejak awal hari $dari sampai sekarang. */
    public function omzet(CarbonInterface $dari): int
    {
        return (int) $this->dibayarSejak($dari)->sum(DB::raw(self::OMZET));
    }

    /**
     * Omzet setiap hari selama $hari hari terakhir sampai hari ini, urut dari yang terlama. Hari tanpa penjualan bernilai 0.
     *
     * @return array<string, int> tanggal Y-m-d => omzet
     */
    public function omzetHarian(int $hari): array
    {
        $dari = today()->subDays($hari - 1);
        $omzet = $this->dibayarSejak($dari)->toBase()
            ->selectRaw('date(dibayar_at) as tanggal')->selectRaw('sum('.self::OMZET.') as omzet')
            ->groupBy('tanggal')
            ->pluck('omzet', 'tanggal');

        return collect(range(0, $hari - 1))
            ->mapWithKeys(fn ($i) => [$tanggal = $dari->copy()->addDays($i)->toDateString() => (int) ($omzet[$tanggal] ?? 0)])
            ->all();
    }

    /**
     * Produk dengan barang terjual terbanyak dari pesanan yang dibayar sejak $dari. Dikelompokkan menurut nama
     * produk di Item pesanan, jadi produk yang sudah dihapus dari katalog tetap terhitung.
     *
     * @return Collection<int, object{nama_produk: string, terjual: int}>
     */
    public function terlaris(CarbonInterface $dari, int $batas = 5): Collection
    {
        return ItemPesanan::toBase()
            ->selectRaw('nama_produk, sum(jumlah) as terjual')
            ->whereIn('pesanan_id', $this->dibayarSejak($dari)->select('id'))
            ->groupBy('nama_produk')
            ->orderByDesc('terjual')->orderBy('nama_produk')
            ->limit($batas)
            ->get()
            ->each(fn ($baris) => $baris->terjual = (int) $baris->terjual);
    }

    /**
     * Jumlah pesanan di setiap status, termasuk status yang belum punya pesanan.
     *
     * @return array<string, int> nilai StatusPesanan => jumlah
     */
    public function jumlahPerStatus(): array
    {
        $jumlah = Pesanan::toBase()->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');

        return collect(StatusPesanan::cases())->mapWithKeys(fn ($status) => [$status->value => (int) ($jumlah[$status->value] ?? 0)])->all();
    }

    private function dibayarSejak(CarbonInterface $dari): Builder
    {
        return Pesanan::status(...StatusPesanan::sudahDibayar())->where('dibayar_at', '>=', $dari->copy()->startOfDay());
    }
}
