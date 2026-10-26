<?php

namespace App\Models;

use App\Exceptions\KeranjangDitolak;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Keranjang pengunjung: milik pelanggan yang masuk, atau milik tamu lewat token di sesi.
 * Keranjang tidak pernah mengubah stok; stok hanya dipakai sebagai batas jumlah.
 */
#[Fillable(['user_id', 'token'])]
class Keranjang extends Model
{
    // kunci sesi tempat token keranjang tamu disimpan
    public const KUNCI_SESI = 'keranjang_token';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemKeranjang::class)->orderBy('id');
    }

    // keranjang pengunjung saat ini, tanpa membuat yang baru
    public static function milikPengunjung(): ?self
    {
        if ($user = Auth::guard('web')->user()) {
            return static::firstWhere('user_id', $user->id);
        }

        $token = session(self::KUNCI_SESI);

        return $token ? static::firstWhere('token', $token) : null;
    }

    public static function milikPengunjungAtauBaru(): self
    {
        if ($user = Auth::guard('web')->user()) {
            return static::firstOrCreate(['user_id' => $user->id]);
        }

        return static::milikPengunjung()
            ?? static::create(['token' => tap(Str::random(40), fn ($token) => session()->put(self::KUNCI_SESI, $token))]);
    }

    /**
     * Menambah varian ke keranjang. Jumlah totalnya (yang sudah ada + yang baru) tidak boleh melebihi stok.
     *
     * @throws KeranjangDitolak
     */
    public function tambah(Varian $varian, int $jumlah): ItemKeranjang
    {
        return $this->dikunci(function () use ($varian, $jumlah) {
            $item = $this->items()->firstOrNew(['varian_id' => $varian->id]);
            $varian->pastikanBisaDipesan($jumlah, $item->jumlah ?? 0);

            $item->jumlah = ($item->jumlah ?? 0) + $jumlah;
            $item->save();

            return $item;
        });
    }

    /**
     * Jumlah 0 menghapus item. Menambah jumlah diperiksa terhadap stok; mengurangi selalu boleh.
     *
     * @throws KeranjangDitolak
     */
    public function ubahJumlah(int $itemId, int $jumlah): void
    {
        $this->dikunci(function () use ($itemId, $jumlah) {
            $item = $this->items()->with('varian')->findOrFail($itemId);

            if ($jumlah < 1) {
                $item->delete();

                return;
            }

            if ($jumlah > $item->jumlah) {
                $item->varian->pastikanBisaDipesan($jumlah);
            }

            $item->update(['jumlah' => $jumlah]);
        });
    }

    public function hapus(int $itemId): void
    {
        $this->items()->whereKey($itemId)->delete();
    }

    /**
     * Memindahkan isi keranjang tamu ke keranjang ini. Jumlah varian yang sama dijumlahkan dan dibatasi stok;
     * varian yang sudah tidak dijual tidak ikut.
     */
    public function gabungkan(self $tamu): void
    {
        $this->dikunci(function () use ($tamu) {
            foreach ($tamu->items()->with('varian')->get() as $itemTamu) {
                if (! $itemTamu->varian->dijual()) {
                    continue;
                }

                $item = $this->items()->firstOrNew(['varian_id' => $itemTamu->varian_id]);
                $jumlah = min(($item->jumlah ?? 0) + $itemTamu->jumlah, $itemTamu->varian->stok);

                if ($jumlah > 0) {
                    $item->jumlah = $jumlah;
                    $item->save();
                }
            }

            $tamu->delete();
        });
    }

    public function subtotal(): int
    {
        return $this->items->sum(fn (ItemKeranjang $item) => $item->jumlahHarga());
    }

    public function jumlahBarang(): int
    {
        return $this->items->sum('jumlah');
    }

    // perubahan isi keranjang dijalankan bergantian, supaya klik ganda tidak menabrak indeks unik item
    protected function dikunci(callable $ubah): mixed
    {
        return DB::transaction(function () use ($ubah) {
            static::whereKey($this->id)->lockForUpdate()->first();

            return $ubah();
        });
    }
}
