<?php

namespace App\Livewire;

use App\Exceptions\KeranjangDitolak;
use App\Livewire\Forms\AlamatForm;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Pengiriman\OngkirGagal;
use App\Pengiriman\ShippingProvider;
use App\Pengiriman\Tarif;
use App\Support\Format;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Checkout extends Component
{
    public $alamatId;

    public AlamatForm $form;

    // formulir alamat baru terbuka di halaman checkout
    public $tambahAlamat = false;

    // kunci tarif yang dipilih, mis. "jne:reg"
    public $kurir = null;

    // hanya untuk ditampilkan; saat membayar, tarif diminta ulang ke ShippingProvider
    #[Locked]
    public array $pilihanKurir = [];

    // alasan pilihan kurir tidak bisa ditampilkan, dan apakah pembeli perlu mengubah alamatnya
    #[Locked]
    public ?string $pesanOngkir = null;

    #[Locked]
    public bool $ongkirPerluUbahAlamat = false;

    public function mount()
    {
        if (! Auth::user()) {
            return redirect()->guest(route('login'));
        }

        if (! Keranjang::milikPengunjung()?->items()->exists()) {
            return redirect()->route('keranjang');
        }

        $this->alamatId = Auth::user()->alamats()->value('id');
        $this->tambahAlamat = ! $this->alamatId;
        $this->muatOngkir();
    }

    public function updatedAlamatId()
    {
        $this->muatOngkir();
    }

    public function simpanAlamat()
    {
        $this->alamatId = $this->form->simpan(Auth::user())->id;
        $this->tambahAlamat = false;
        $this->muatOngkir();
    }

    public function bayar()
    {
        $this->validate([
            'alamatId' => ['required', Rule::exists('alamats', 'id')->where('user_id', Auth::id())],
            'kurir' => ['required', 'string'],
        ], [
            'alamatId.required' => 'Pilih atau tambahkan alamat pengiriman dulu.',
            'kurir.required' => 'Pilih kurir pengiriman dulu.',
        ]);

        $keranjang = Keranjang::milikPengunjung();
        if (! $keranjang?->items()->exists()) {
            return redirect()->route('keranjang');
        }

        // ongkir dikonfirmasi ulang ke provider; harga yang tampil di browser tidak dipakai
        try {
            $tarif = Tarif::cari($this->tarifSekarang(), $this->kurir);
        } catch (OngkirGagal $e) {
            $this->pesanOngkir = $e->getMessage();

            return $this->addError('kurir', $e->getMessage());
        }

        if (! $tarif) {
            $this->muatOngkir();

            return $this->addError('kurir', 'Layanan kurir itu tidak tersedia lagi. Pilih kurir lain.');
        }

        // tarif berubah sejak ditampilkan: tunjukkan harga baru dulu, pembeli menekan Bayar sekali lagi
        $tampil = collect($this->pilihanKurir)->firstWhere('kunci', $tarif->kunci());
        if ($tampil['ongkir'] !== $tarif->ongkir) {
            $this->muatOngkir();

            return $this->addError('kurir', 'Ongkir '.$tarif->label().' berubah menjadi '.Format::rupiah($tarif->ongkir).'. Periksa totalnya, lalu bayar lagi.');
        }

        try {
            $pesanan = Pesanan::buatDariKeranjang($keranjang, Auth::user()->alamats()->find($this->alamatId), $tarif);
        } catch (KeranjangDitolak $e) {
            $this->muatOngkir();

            return $this->addError('keranjang', $e->getMessage());
        }

        $this->dispatch('keranjang-diperbarui');

        // halaman detail langsung membuka jendela pembayaran
        return redirect()->to(route('pesanan.detail', $pesanan).'?bayar=1');
    }

    public function muatOngkir(): void
    {
        $this->pilihanKurir = [];
        $this->pesanOngkir = null;
        $this->ongkirPerluUbahAlamat = false;

        try {
            $this->pilihanKurir = array_map(fn (Tarif $tarif) => [
                'kunci' => $tarif->kunci(),
                'label' => $tarif->label(),
                'ongkir' => $tarif->ongkir,
                'estimasi' => $tarif->estimasi,
            ], $this->tarifSekarang());

            if (! $this->pilihanKurir && $this->alamatId && Keranjang::milikPengunjung()?->items()->exists()) {
                throw OngkirGagal::tanpaKurir();
            }
        } catch (OngkirGagal $e) {
            if ($e->getPrevious()) {
                report($e);
            }
            $this->pesanOngkir = $e->getMessage();
            $this->ongkirPerluUbahAlamat = $e->perluUbahAlamat;
        }

        if (! collect($this->pilihanKurir)->contains('kunci', $this->kurir)) {
            $this->kurir = null;
        }
    }

    /**
     * Tarif dari provider untuk alamat terpilih dan isi tas saat ini.
     *
     * @return list<Tarif>
     *
     * @throws OngkirGagal
     */
    protected function tarifSekarang(): array
    {
        $alamat = Auth::user()->alamats()->find($this->alamatId);
        $keranjang = Keranjang::milikPengunjung()?->load('items.varian.produk');
        if (! $alamat || ! $keranjang || $keranjang->items->isEmpty()) {
            return [];
        }

        if (! $alamat->area_id) {
            throw OngkirGagal::tanpaArea();
        }

        return app(ShippingProvider::class)->tarif($alamat->area_id, $keranjang->barangKiriman());
    }

    public function render()
    {
        $keranjang = Keranjang::milikPengunjung();
        $keranjang?->load('items.varian.produk.fotoUtama');
        $ongkir = collect($this->pilihanKurir)->firstWhere('kunci', $this->kurir)['ongkir'] ?? null;

        return view('livewire.checkout', [
            'keranjang' => $keranjang,
            'alamats' => Auth::user()->alamats,
            'subtotal' => $keranjang?->subtotal() ?? 0,
            'ongkir' => $ongkir,
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
