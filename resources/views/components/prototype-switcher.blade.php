{{-- PROTOTYPE: bar pengganti varian beranda (#11). Hanya tampil di APP_ENV=local dan di route home. --}}
@php
    $daftar = ['' => 'Sekarang', 'A' => 'Surat Cinta', 'B' => 'Hampers', 'C' => 'Studio'];
    $kini = in_array(request('variant'), ['A', 'B', 'C']) ? request('variant') : '';
@endphp

@if (app()->environment('local') && request()->routeIs('home'))
    <div x-data="{
            keys: {{ json_encode(array_keys($daftar)) }},
            kini: '{{ $kini }}',
            pergi(arah) {
                const i = (this.keys.indexOf(this.kini) + arah + this.keys.length) % this.keys.length;
                const url = new URL(window.location);
                this.keys[i] ? url.searchParams.set('variant', this.keys[i]) : url.searchParams.delete('variant');
                window.location = url;
            },
        }"
        @keydown.window="if (! $event.target.closest('input, textarea, select, [contenteditable]')) {
            if ($event.key === 'ArrowLeft') pergi(-1);
            if ($event.key === 'ArrowRight') pergi(1);
        }"
        style="position:fixed;left:50%;bottom:16px;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:4px;padding:4px;border-radius:999px;background:#000;color:#fff;font:600 13px/1 system-ui,sans-serif;box-shadow:0 6px 24px rgba(0,0,0,.35);outline:2px solid #ff00aa;">
        <button type="button" @click="pergi(-1)" aria-label="Varian sebelumnya"
            style="width:32px;height:32px;border-radius:999px;background:#222;color:#fff;border:0;cursor:pointer;">&larr;</button>
        <span style="padding:0 10px;white-space:nowrap;">
            Prototipe: {{ $kini ?: '0' }} ({{ $daftar[$kini] }})
        </span>
        <button type="button" @click="pergi(1)" aria-label="Varian berikutnya"
            style="width:32px;height:32px;border-radius:999px;background:#222;color:#fff;border:0;cursor:pointer;">&rarr;</button>
    </div>
@endif
