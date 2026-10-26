<?php

namespace App\Livewire\Forms;

use App\Models\Alamat;
use App\Models\User;
use Livewire\Form;

class AlamatForm extends Form
{
    public ?Alamat $alamat = null;

    public $label = 'Rumah';

    public $penerima = '';

    public $no_hp = '';

    public $alamat_lengkap = '';

    public $kode_pos = '';

    public $utama = false;

    public function isi(Alamat $alamat): void
    {
        $this->alamat = $alamat;
        $this->fill([
            'label' => $alamat->label,
            'penerima' => $alamat->penerima,
            'no_hp' => $alamat->no_hp,
            'alamat_lengkap' => $alamat->alamat,
            'kode_pos' => $alamat->kode_pos,
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
            'kode_pos' => ['required', 'digits:5'],
            'utama' => ['boolean'],
        ], [
            'no_hp.regex' => 'No. HP harus diawali 08 / 62 / +62 dan terdiri dari 10–13 digit.',
            'kode_pos.digits' => 'Kode pos terdiri dari 5 angka.',
        ]);

        $alamat = $this->alamat ?? $user->alamats()->make();
        $alamat->fill([
            'label' => $data['label'],
            'penerima' => $data['penerima'],
            'no_hp' => $data['no_hp'],
            'alamat' => $data['alamat_lengkap'],
            'kode_pos' => $data['kode_pos'],
        ])->save();

        // alamat pertama otomatis menjadi utama
        if ($data['utama'] || ! $user->alamats()->whereKeyNot($alamat->id)->exists()) {
            $user->jadikanAlamatUtama($alamat);
        }

        $this->reset();

        return $alamat;
    }
}
