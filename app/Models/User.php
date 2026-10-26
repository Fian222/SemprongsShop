<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class)->latest('id');
    }

    // tepat satu alamat utama selama pelanggan punya alamat
    public function jadikanAlamatUtama(Alamat $alamat): void
    {
        $this->alamats()->whereKeyNot($alamat->id)->update(['utama' => false]);
        $alamat->update(['utama' => true]);
    }

    public function hapusAlamat(Alamat $alamat): void
    {
        $alamat->delete();

        if ($alamat->utama && $pengganti = $this->alamats()->oldest('id')->first()) {
            $this->jadikanAlamatUtama($pengganti);
        }
    }

    public function alamats()
    {
        // alamat utama selalu di urutan pertama
        return $this->hasMany(Alamat::class)->orderByDesc('utama')->orderBy('id');
    }
}
