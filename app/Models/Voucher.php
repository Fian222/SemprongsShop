<?php

namespace App\Models;

use App\Enums\StatusPesanan;
use App\Enums\TipeVoucher;
use App\Exceptions\VoucherDitolak;
use App\Support\Format;
use Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Kode promo yang memotong Subtotal. Pemakaiannya dihitung dari Pesanan, bukan disimpan sebagai angka:
 * pesanan yang menunggu pembayaran memegang satu kuota, dan kuota itu lepas sendiri saat pesanannya
 * kedaluwarsa atau dibatalkan. Hanya pesanan yang sudah dibayar yang dihitung sebagai pemakaian.
 */
#[Fillable(['kode', 'tipe', 'nilai', 'maks_diskon', 'min_subtotal', 'kuota', 'mulai', 'berakhir', 'aktif'])]
class Voucher extends Model
{
    /** @use HasFactory<VoucherFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tipe' => TipeVoucher::class,
            'nilai' => 'integer',
            'maks_diskon' => 'integer',
            'min_subtotal' => 'integer',
            'kuota' => 'integer',
            'mulai' => 'datetime',
            'berakhir' => 'datetime',
            'aktif' => 'boolean',
        ];
    }

    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class);
    }

    // kode disimpan dan dicari dalam huruf besar, jadi pembeli boleh mengetik " hemat10 "
    public static function normalisasiKode(string $kode): string
    {
        return Str::upper(trim($kode));
    }

    /**
     * @throws VoucherDitolak
     */
    public static function cari(string $kode, bool $kunci = false): self
    {
        $kode = static::normalisasiKode($kode);

        return static::where('kode', $kode)->when($kunci, fn (Builder $q) => $q->lockForUpdate())->first()
            ?? throw VoucherDitolak::tidakDikenal($kode);
    }

    // kolom terpakai: jumlah pesanan yang sudah dibayar dengan voucher ini
    public function scopeDenganPemakaian(Builder $query): void
    {
        $query->withCount(['pesanans as terpakai' => fn (Builder $q) => $q->status(...StatusPesanan::sudahDibayar())]);
    }

    /**
     * Diskon voucher ini untuk $subtotal. Diskon persen dibatasi maks_diskon, dan diskon tidak pernah melebihi subtotal.
     *
     * @throws VoucherDitolak bila voucher nonaktif, di luar masa berlaku, kuotanya habis, atau subtotal di bawah minimum
     */
    public function diskonUntuk(int $subtotal): int
    {
        $this->pastikanBerlaku($subtotal);

        $diskon = match ($this->tipe) {
            TipeVoucher::Persen => intdiv($subtotal * $this->nilai, 100),
            TipeVoucher::Nominal => $this->nilai,
        };

        if ($this->maks_diskon !== null) {
            $diskon = min($diskon, $this->maks_diskon);
        }

        return min($diskon, $subtotal);
    }

    // mis. "10%, maks. Rp 15.000" atau "Rp 20.000"
    public function ringkasan(): string
    {
        return match ($this->tipe) {
            TipeVoucher::Persen => $this->nilai.'%'.($this->maks_diskon ? ', maks. '.Format::rupiah($this->maks_diskon) : ''),
            TipeVoucher::Nominal => Format::rupiah($this->nilai),
        };
    }

    /**
     * Keadaan voucher untuk daftar admin, sebagai [label, kelas badge].
     *
     * @return array{string, string}
     */
    public function keadaan(): array
    {
        return match (true) {
            ! $this->aktif => ['Nonaktif', 'badge-out'],
            (bool) $this->berakhir?->isPast() => ['Berakhir', 'badge-out'],
            (bool) $this->mulai?->isFuture() => ['Terjadwal', 'badge-low'],
            default => ['Aktif', 'badge-ok'],
        };
    }

    /**
     * @throws VoucherDitolak
     */
    protected function pastikanBerlaku(int $subtotal): void
    {
        if (! $this->aktif) {
            throw VoucherDitolak::nonaktif($this);
        }

        if ($this->mulai?->isFuture()) {
            throw VoucherDitolak::belumMulai($this);
        }

        if ($this->berakhir?->isPast()) {
            throw VoucherDitolak::berakhir($this);
        }

        // pesanan yang masih menunggu pembayaran ikut memegang kuota, supaya kuota tidak terlampaui saat semuanya dibayar
        if ($this->kuota !== null && $this->pesanans()->status(...StatusPesanan::memegangKuota())->count() >= $this->kuota) {
            throw VoucherDitolak::kuotaHabis($this);
        }

        if ($subtotal < $this->min_subtotal) {
            throw VoucherDitolak::diBawahMinimum($this, $subtotal);
        }
    }
}
