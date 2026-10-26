<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['label', 'penerima', 'no_hp', 'alamat', 'kode_pos', 'utama'])]
class Alamat extends Model
{
    protected function casts(): array
    {
        return ['utama' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // salinan yang disimpan di pesanan
    public function salinan(): array
    {
        return $this->only('label', 'penerima', 'no_hp', 'alamat', 'kode_pos');
    }
}
