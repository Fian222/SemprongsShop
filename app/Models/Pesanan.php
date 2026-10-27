<?php

namespace App\Models;

use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Exceptions\KeranjangDitolak;
use App\Exceptions\TransisiTidakSah;
use App\Exceptions\VoucherDitolak;
use App\Mail\PembayaranDiterima;
use App\Mail\PesananDikirim;
use App\Pembayaran\PaymentGateway;
use App\Pengiriman\Pelacakan;
use App\Pengiriman\PelacakanGagal;
use App\Pengiriman\ShippingProvider;
use App\Pengiriman\Tarif;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

#[Fillable(['nomor', 'user_id', 'alamat', 'subtotal', 'ongkir', 'diskon', 'total', 'voucher_id', 'voucher_kode', 'status', 'batas_bayar'])]
class Pesanan extends Model
{
    protected function casts(): array
    {
        return [
            'alamat' => 'array',
            'subtotal' => 'integer',
            'ongkir' => 'integer',
            'diskon' => 'integer',
            'total' => 'integer',
            'status' => StatusPesanan::class,
            'batas_bayar' => 'datetime',
            'dibayar_at' => 'datetime',
            'diproses_at' => 'datetime',
            'dikirim_at' => 'datetime',
            'selesai_at' => 'datetime',
            'kedaluwarsa_at' => 'datetime',
            'dibatalkan_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'nomor';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemPesanan::class)->orderBy('id');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function pengiriman(): HasOne
    {
        return $this->hasOne(Pengiriman::class);
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class);
    }

    public function scopeStatus(Builder $query, StatusPesanan ...$status): void
    {
        $query->whereIn('status', $status);
    }

    /**
     * Membuat pesanan dari isi keranjang dan memesan stoknya dalam satu transaksi. Baris varian dikunci,
     * jadi dua checkout yang berebut stok terakhir tidak bisa sama-sama berhasil. Harga dihitung ulang dari varian;
     * ongkir datang dari $tarif yang baru saja dikonfirmasi ke ShippingProvider untuk berat keranjang ini.
     * Diskon $kodeVoucher dihitung ulang dari subtotal itu, dengan baris voucher dikunci supaya kuotanya tidak terlampaui.
     *
     * @throws KeranjangDitolak
     * @throws VoucherDitolak
     */
    public static function buatDariKeranjang(Keranjang $keranjang, Alamat $alamat, Tarif $tarif, ?string $kodeVoucher = null): self
    {
        // dua checkout pertama di hari yang sama bisa mendapat nomor yang sama; yang kalah mencoba lagi
        return retry(3, fn () => DB::transaction(fn () => static::pesanDariKeranjang($keranjang, $alamat, $tarif, $kodeVoucher), 3),
            when: fn ($e) => $e instanceof UniqueConstraintViolationException);
    }

    protected static function pesanDariKeranjang(Keranjang $keranjang, Alamat $alamat, Tarif $tarif, ?string $kodeVoucher): self
    {
        // voucher dikunci sebelum bacaan biasa mana pun: di REPEATABLE READ bacaan biasa pertama membekukan snapshot,
        // dan hitungan kuota harus melihat pesanan checkout lain yang baru saja selesai memegang kunci ini
        $voucher = $kodeVoucher === null ? null : Voucher::cari($kodeVoucher, kunci: true);

        $items = $keranjang->items()->lockForUpdate()->get();
        // klik ganda: request kedua mendapati keranjang yang sudah dikosongkan request pertama
        if ($items->isEmpty()) {
            throw KeranjangDitolak::kosong();
        }

        $varians = Varian::with('produk.fotoUtama')->whereIn('id', $items->pluck('varian_id'))
            ->lockForUpdate()->get()->keyBy('id');

        foreach ($items as $item) {
            $varians[$item->varian_id]->pastikanBisaDipesan($item->jumlah);
        }

        // isi tas berubah (tab lain) sesudah ongkir dihitung: ongkirnya tidak lagi sesuai
        if ($items->sum(fn ($item) => $varians[$item->varian_id]->berat * $item->jumlah) !== $tarif->berat) {
            throw KeranjangDitolak::beratBerubah();
        }

        $subtotal = $items->sum(fn ($item) => $varians[$item->varian_id]->harga * $item->jumlah);
        $diskon = $voucher?->diskonUntuk($subtotal) ?? 0;

        $pesanan = static::create([
            'nomor' => static::nomorBaru(),
            'user_id' => $alamat->user_id,
            'alamat' => $alamat->salinan(),
            'subtotal' => $subtotal,
            'ongkir' => $tarif->ongkir,
            'diskon' => $diskon,
            'total' => $subtotal + $tarif->ongkir - $diskon,
            'voucher_id' => $voucher?->id,
            'voucher_kode' => $voucher?->kode,
            'status' => StatusPesanan::MenungguPembayaran,
            'batas_bayar' => now()->addHours(config('pembayaran.batas_bayar_jam')),
        ]);

        foreach ($items as $item) {
            $varian = $varians[$item->varian_id];
            $pesanan->items()->create([
                'varian_id' => $varian->id,
                'nama_produk' => $varian->produk->nama,
                'nama_varian' => $varian->nama,
                'sku' => $varian->sku,
                'foto' => $varian->produk->fotoUtama?->path,
                'harga' => $varian->harga,
                'jumlah' => $item->jumlah,
                'berat' => $varian->berat,
            ]);
            $varian->decrement('stok', $item->jumlah);
        }

        $pesanan->pengiriman()->save(Pengiriman::dariTarif($tarif));
        $keranjang->items()->delete();

        return $pesanan;
    }

    /**
     * Satu-satunya jalan mengubah status. Transisi di luar mesin status ditolak; kedaluwarsa dan dibatalkan
     * mengembalikan stok yang tadinya dipesan.
     *
     * @throws TransisiTidakSah
     */
    public function ubahStatus(StatusPesanan $tujuan): void
    {
        DB::transaction(function () use ($tujuan) {
            // baca ulang status di bawah kunci; notifikasi ganda yang datang bersamaan menunggu di sini
            $this->status = static::whereKey($this->id)->lockForUpdate()->first(['status'])->status;

            if (! $this->status->bisaMenjadi($tujuan)) {
                throw new TransisiTidakSah($this->status, $tujuan);
            }

            if ($tujuan === StatusPesanan::Dikirim && blank($this->pengiriman()->value('resi'))) {
                throw new TransisiTidakSah($this->status, $tujuan, 'Pesanan baru bisa dikirim dengan nomor resi.');
            }

            if ($tujuan->mengembalikanStok()) {
                foreach ($this->items()->whereNotNull('varian_id')->get() as $item) {
                    Varian::whereKey($item->varian_id)->increment('stok', $item->jumlah);
                }
            }

            $this->status = $tujuan;
            $this->{$tujuan->kolomWaktu()} = now();
            $this->save();

            // di dalam transaksi berkunci: notifikasi ganda kalah di pengecekan transisi, jadi email terkirim sekali
            $email = match ($tujuan) {
                StatusPesanan::Dibayar => new PembayaranDiterima($this),
                StatusPesanan::Dikirim => new PesananDikirim($this),
                default => null,
            };
            if ($email) {
                Mail::to($this->user)->queue($email);
            }
        });
    }

    /**
     * Menandai pesanan Dikirim dengan nomor resi dari kurir. Resi ikut batal tersimpan bila transisinya ditolak.
     *
     * @throws TransisiTidakSah
     */
    public function kirim(string $resi): void
    {
        DB::transaction(function () use ($resi) {
            // pesanan tanpa Pengiriman tetap ditolak ubahStatus karena tidak punya resi
            $this->pengiriman?->update(['resi' => $resi]);
            $this->ubahStatus(StatusPesanan::Dikirim);
        });
    }

    /**
     * Riwayat perjalanan paket dari kurir, atau null bila belum ada resi. Status lacak terakhir disimpan di Pengiriman,
     * dan paket yang sudah diterima membuat pesanan Dikirim menjadi Selesai.
     *
     * @throws PelacakanGagal
     */
    public function lacakPaket(ShippingProvider $provider): ?Pelacakan
    {
        $pengiriman = $this->pengiriman;
        if (blank($pengiriman?->resi)) {
            return null;
        }

        // riwayat dari kurir jarang berubah dalam hitungan menit; pelacakan yang gagal tidak disimpan
        $pelacakan = Pelacakan::dariArray(Cache::remember("pelacakan:{$pengiriman->kurir}:{$pengiriman->resi}", now()->addMinutes(15),
            fn () => $provider->lacak($pengiriman->kurir, $pengiriman->resi)->keArray()));

        $pengiriman->catatPelacakan($pelacakan);

        if ($pelacakan->sampai && $this->status === StatusPesanan::Dikirim) {
            try {
                $this->ubahStatus(StatusPesanan::Selesai);
            } catch (TransisiTidakSah) {
                // sudah diselesaikan admin atau pelacakan lain di saat yang sama
            }
        }

        return $pelacakan;
    }

    /**
     * Transaksi pembayaran pesanan ini. Token yang sudah ada dipakai lagi, jadi pembeli bisa membuka ulang
     * jendela pembayaran yang tadi tertutup. Percobaan yang gagal di tengah jalan diulang dengan referensi baru,
     * karena gateway menolak order_id yang pernah dipakai.
     */
    public function siapkanPembayaran(PaymentGateway $gateway): Pembayaran
    {
        $pembayaran = $this->pembayaran ?? $this->pembayaran()->make([
            'provider' => $gateway->nama(),
            'status' => StatusPembayaran::Menunggu,
            'nominal' => $this->total,
        ]);

        if ($pembayaran->token) {
            return $pembayaran;
        }

        $pembayaran->referensi = $pembayaran->referensi ? $this->nomor.'-'.Str::lower(Str::random(4)) : $this->nomor;
        $pembayaran->save();
        $this->setRelation('pembayaran', $pembayaran);

        $transaksi = $gateway->buatTransaksi($this, $pembayaran->referensi);
        $pembayaran->update(['token' => $transaksi->token, 'redirect_url' => $transaksi->redirectUrl]);

        return $pembayaran;
    }

    public function milik(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }

    public function menungguPembayaran(): bool
    {
        return $this->status === StatusPesanan::MenungguPembayaran && $this->batas_bayar->isFuture();
    }

    // SMP-20261026-0001: nomor urut per hari. Dipanggil di dalam transaksi buatDariKeranjang().
    protected static function nomorBaru(): string
    {
        $awalan = 'SMP-'.now()->format('Ymd').'-';
        $terakhir = static::where('nomor', 'like', $awalan.'%')->lockForUpdate()->max('nomor');

        return $awalan.str_pad((int) substr($terakhir ?? '0', -4) + 1, 4, '0', STR_PAD_LEFT);
    }
}
