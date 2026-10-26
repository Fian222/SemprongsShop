<div role="group" aria-labelledby="label-area">
    @if ($area)
        <div class="flex items-center gap-3 rounded-xs border border-line bg-surface-muted px-4 py-3">
            <x-icon name="map-pin" class="size-5 shrink-0 text-brand" />
            <span class="flex-1 text-[15px]">{{ $area['nama'] }}</span>
            <button type="button" wire:click="ganti" class="text-sm font-medium text-ink-muted underline-offset-2 hover:text-ink hover:underline">Ganti area</button>
        </div>
    @else
        <div class="relative">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-ink-muted" />
            <input id="alamat-area" type="search" wire:model.live.debounce.400ms="cari" autocomplete="off"
                placeholder="Ketik kecamatan atau kota, mis. Coblong" aria-labelledby="label-area" aria-describedby="alamat-area-petunjuk"
                @if ($hasil) aria-controls="hasil-area" @endif class="input pl-11">
        </div>
        <p id="alamat-area-petunjuk" class="hint">Dipakai menghitung ongkir dan kode pos. Ketik minimal 3 huruf.</p>
        <div wire:loading wire:target="cari" class="mt-2 text-sm text-ink-muted">Mencari…</div>
        @if ($pesan)
            <p class="mt-2 text-sm text-ink-muted" role="status">{{ $pesan }}</p>
        @endif
        @if ($hasil)
            <ul id="hasil-area" class="card mt-2 max-h-64 divide-y divide-line overflow-y-auto">
                @foreach ($hasil as $i => $pilihan)
                    <li wire:key="area-{{ $pilihan['id'] }}">
                        <button type="button" wire:click="pilih({{ $i }})"
                            class="w-full px-4 py-3 text-left text-[15px] hover:bg-surface-muted focus-visible:bg-surface-muted">{{ $pilihan['nama'] }}</button>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
