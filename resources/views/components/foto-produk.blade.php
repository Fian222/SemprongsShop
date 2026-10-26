{{-- Foto produk, atau bidang champagne berinisial bila produk belum punya foto --}}
@props(['foto', 'alt' => ''])

@if ($foto)
    <img src="{{ $foto->url() }}" alt="{{ $alt }}" {{ $attributes->class(['object-cover']) }}>
@else
    <div role="img" aria-label="{{ $alt }}" {{ $attributes->except('loading')->class(['@container grid place-items-center bg-champ/60 font-display text-on-champ/60 italic']) }}>
        <span class="text-[40cqi] leading-none" aria-hidden="true">S</span>
    </div>
@endif
