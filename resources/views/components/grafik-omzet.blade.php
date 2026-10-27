{{--
    Grafik batang omzet harian ($harian: tanggal Y-m-d => omzet, urut dari yang terlama). Warna memakai token tema,
    jadi ikut berganti di dark mode. Nilai tiap hari muncul saat batang disorot dan selalu ada di tabel di bawahnya.
--}}
@props(['harian'])

@php
    $tertinggi = max($harian ?: [0]);
    // sumbu dibulatkan ke kelipatan 1, 2, atau 5 × 10^n supaya labelnya angka bulat
    $langkah = 0;
    if ($tertinggi > 0) {
        $pangkat = 10 ** floor(log10($tertinggi / 4));
        $langkah = max(1, (int) ($pangkat * collect([1, 2, 5, 10])->first(fn ($k) => $k * $pangkat >= $tertinggi / 4)));
    }
    $puncak = $langkah ? (int) ceil($tertinggi / $langkah) * $langkah : 0;
    $garis = $langkah ? range(0, $puncak, $langkah) : [0];
    $jumlahHari = count($harian);
    $tanggal = fn ($t) => \Illuminate\Support\Carbon::parse($t)->locale('id');
    $hariPuncak = $tertinggi > 0 ? array_search($tertinggi, $harian, true) : null;
    // tinggi dari garis nol, dalam persen tinggi grafik
    $persen = fn ($nilai) => $puncak ? $nilai / $puncak * 100 : 0;
@endphp

<div {{ $attributes }}>
    <figure>
        <div class="flex gap-3" role="img"
            aria-label="Grafik omzet harian {{ $jumlahHari }} hari. {{ $hariPuncak ? 'Tertinggi '.\App\Support\Format::rupiah($tertinggi).' pada '.$tanggal($hariPuncak)->translatedFormat('j M Y').'.' : 'Belum ada omzet.' }}">
            {{-- sumbu nilai --}}
            <div class="relative h-52 w-16 shrink-0 text-right text-xs text-ink-muted tabular-nums" aria-hidden="true">
                @foreach ($garis as $nilai)
                    <span class="absolute right-0 translate-y-1/2" style="bottom: {{ $persen($nilai) }}%">
                        {{ \App\Support\Format::rupiahRingkas($nilai) }}
                    </span>
                @endforeach
            </div>

            <div class="min-w-0 flex-1">
                <div class="relative h-52" aria-hidden="true">
                    @foreach ($garis as $nilai)
                        <div class="absolute inset-x-0 border-t border-line" style="bottom: {{ $persen($nilai) }}%"></div>
                    @endforeach

                    <div class="absolute inset-0 flex items-end">
                        @foreach ($harian as $hari => $omzet)
                            <div wire:key="omzet-{{ $hari }}" class="group relative flex h-full flex-1 items-end justify-center px-px">
                                <div @class([
                                    'w-full max-w-6 rounded-t-[4px] bg-brand transition-colors group-hover:bg-brand-hover',
                                    'min-h-0.5' => $omzet > 0,
                                ]) style="height: {{ $persen($omzet) }}%"></div>

                                <div @class([
                                    'pointer-events-none absolute bottom-full z-10 mb-2 hidden rounded-xs bg-ink px-3 py-2 text-xs whitespace-nowrap text-canvas group-hover:block',
                                    'left-0' => $loop->index < 4,
                                    'right-0' => $loop->index >= $jumlahHari - 4,
                                    'left-1/2 -translate-x-1/2' => $loop->index >= 4 && $loop->index < $jumlahHari - 4,
                                ])>
                                    <strong class="block text-sm font-medium tabular-nums">@rupiah($omzet)</strong>
                                    {{ $tanggal($hari)->translatedFormat('l, j M') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- sumbu tanggal: awal, tiap pekan, dan hari ini --}}
                <div class="relative mt-2 h-4 text-xs text-ink-muted" aria-hidden="true">
                    @foreach (array_keys($harian) as $i => $hari)
                        @if ($i % 7 === 0 && $i < $jumlahHari - 4 || $i === $jumlahHari - 1)
                            <span @class([
                                'absolute whitespace-nowrap',
                                // di layar sempit label pekan ke-1 dan ke-3 bertabrakan dengan tetangganya
                                'max-sm:hidden' => $i % 14 === 7,
                                '-translate-x-1/2' => $i > 0 && $i < $jumlahHari - 1,
                                '-translate-x-full' => $i === $jumlahHari - 1,
                            ]) style="left: {{ $i === $jumlahHari - 1 ? 100 : $i / $jumlahHari * 100 }}%">
                                {{ $i === $jumlahHari - 1 ? 'Hari ini' : $tanggal($hari)->translatedFormat('j M') }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </figure>

    <details class="mt-5 text-sm">
        <summary class="cursor-pointer text-ink-muted hover:text-ink">Lihat sebagai tabel</summary>
        <div class="table-wrap mt-3 max-h-80 overflow-y-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th class="text-right">Omzet</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($harian as $hari => $omzet)
                        <tr wire:key="tabel-omzet-{{ $hari }}">
                            <td>{{ $tanggal($hari)->translatedFormat('j M Y') }}</td>
                            <td class="text-right tabular-nums">@rupiah($omzet)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</div>
