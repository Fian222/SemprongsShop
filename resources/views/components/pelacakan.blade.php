{{-- Resi dan riwayat pelacakan paket. Dipakai komponen Livewire dengan trait MemuatPelacakan. --}}
@props(['pengiriman', 'pelacakan' => null, 'gagal' => null])

<section wire:init="muatPelacakan" {{ $attributes->class('card p-6') }} aria-labelledby="judul-lacak">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 id="judul-lacak" class="flex items-center gap-2 text-xl"><x-icon name="truck" class="size-5 text-brand" /> Lacak paket</h2>
            <p class="mt-1 text-[15px] text-ink-muted">
                {{ $pengiriman->label() }} · resi
                <span class="font-medium text-ink tabular-nums">{{ $pengiriman->resi }}</span>
            </p>
        </div>
        <button type="button" x-data="{ disalin: false }"
            x-on:click="navigator.clipboard.writeText(@js($pengiriman->resi)); disalin = true; setTimeout(() => disalin = false, 2000)"
            class="btn btn-secondary btn-sm">
            <x-icon name="copy" /> <span x-text="disalin ? 'Tersalin' : 'Salin resi'">Salin resi</span>
        </button>
    </div>

    @if ($gagal)
        <p class="mt-4 text-sm text-ink-muted" role="status">{{ $gagal }}</p>
        <button type="button" wire:click="muatPelacakan" wire:loading.attr="disabled" wire:target="muatPelacakan" class="btn btn-secondary btn-sm mt-3">Coba lagi</button>
    @elseif ($pelacakan)
        <p @class(['mt-4 font-medium', 'text-ok' => $pelacakan['sampai']]) role="status">{{ $pelacakan['status'] }}</p>
        @if ($pelacakan['riwayat'])
            <ol class="mt-4 flex flex-col">
                @foreach ($pelacakan['riwayat'] as $kejadian)
                    <li class="relative flex gap-4 pb-5 last:pb-0">
                        @unless ($loop->last)
                            <span class="absolute top-3 left-[5px] h-full w-px bg-line" aria-hidden="true"></span>
                        @endunless
                        <span @class(['relative mt-1.5 size-[11px] shrink-0 rounded-full border-2', 'border-brand bg-brand' => $loop->first, 'border-line-strong bg-surface' => ! $loop->first]) aria-hidden="true"></span>
                        <div class="text-[15px]">
                            <p @class(['font-medium' => $loop->first])>{{ $kejadian['keterangan'] }}</p>
                            <p class="text-sm text-ink-muted">@tanggal($kejadian['waktu'])</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    @else
        <p class="mt-4 text-sm text-ink-muted" role="status">Memuat riwayat pelacakan…</p>
    @endif
</section>
