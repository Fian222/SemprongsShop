{{-- Nama toko dalam Bodoni miring; warna mengikuti induknya kecuali diberi kelas teks --}}
@props(['size' => 'text-[26px]'])

<span {{ $attributes->merge(['class' => "font-display $size leading-none font-semibold italic tracking-tight whitespace-nowrap"]) }}>Semprong</span>
