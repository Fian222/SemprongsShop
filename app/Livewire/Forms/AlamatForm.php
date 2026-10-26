<?php

namespace App\Livewire\Forms;

use App\Models\Alamat;
use App\Models\User;
use App\Pengiriman\Area;
use App\Pengiriman\OngkirGagal;
use App\Pengiriman\ShippingProvider;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class AlamatForm extends Form
{
    public ?Alamat $alamat = null;

    public $label = 'Rumah';

    public $penerima = '';

    public $no_hp = '';

    public $alamat_lengkap = '';

    // area tujuan ongkir: ['id' => ..., 'nama' => ..., 'kode_pos' => ...], diisi komponen PilihArea
    public $area = null;

    public $utama = false;

    public function isi(Alamat $alamat): void
    {
        $this->alamat = $alamat;
        $this->fill([
            'label' => $alamat->label,
            'penerima' => $alamat->penerima,
            'no_hp' => $alamat->no_hp,
            'alamat_lengkap' => $alamat->alamat,
            'area' => $alamat->area_id ? ['id' => $alamat->area_id, 'nama' => $alamat->area_nama, 'kode_pos' => $alamat->kode_pos] : null,
            'utama' => $alamat->utama,
        ]);
    }

    public function simpan(User $user): Alamat
    {
        // pembeli sering menulis nomor dengan spasi/tanda hubung, mis. 0812-3456-7890
        $this->no_hp = preg_replace('/[\s\-]/', '', (string) $this->no_hp);

        $data = $this->validate([
            'label' => ['required', 'string', 'max:50'],
            'penerima' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'regex:/^(\+62|62|0)8[1-9][0-9]{7,10}$/'],
            'alamat_lengkap' => ['required', 'string', 'max:500'],
            'area' => ['required', 'array'],
            'area.id' => ['required', 'string', 'max:100'],
            'area.nama' => ['required', 'string', 'max:255'],
            'utama' => ['boolean'],
        ], [
            'area.required' => 'Pilih area tujuan supaya ongkir bisa dihitung.',
            'no_hp.regex' => 'No. HP harus diawali 08 / 62 / +62 dan terdiri dari 10–13 digit.',
        ]);

        $area = $this->areaDariProvider($data['area']['id'], $data['area']['nama']);

        $alamat = $this->alamat ?? $user->alamats()->make();
        $alamat->fill([
            'label' => $data['label'],
            'penerima' => $data['penerima'],
            'no_hp' => $data['no_hp'],
            'alamat' => $data['alamat_lengkap'],
            // kode pos ikut area, supaya tidak berbeda dengan area yang dipakai menghitung ongkir
            'kode_pos' => (string) $area->kodePos,
            'area_id' => $area->id,
            'area_nama' => $area->nama,
        ])->save();

        // alamat pertama otomatis menjadi utama
        if ($data['utama'] || ! $user->alamats()->whereKeyNot($alamat->id)->exists()) {
            $user->jadikanAlamatUtama($alamat);
        }

        $this->reset();

        return $alamat;
    }

    // area dari browser diperiksa ulang ke provider; ID karangan atau nama yang tidak cocok ditolak
    protected function areaDariProvider(string $id, string $nama): Area
    {
        try {
            $area = collect(app(ShippingProvider::class)->cariArea($nama))->first(fn (Area $a) => $a->id === $id);
        } catch (OngkirGagal $e) {
            report($e);

            throw ValidationException::withMessages(['form.area' => $e->getMessage()]);
        }

        if (! $area) {
            throw ValidationException::withMessages(['form.area' => 'Area tujuan tidak dikenal. Cari dan pilih ulang areanya.']);
        }

        return $area;
    }
}
