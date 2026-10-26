<div>
    <x-admin-heading title="Pesanan" subtitle="Proses pesanan yang sudah dibayar, input resi, dan pantau pengirimannya.">
        <label class="relative block sm:w-72">
            <span class="sr-only">Cari pesanan</span>
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-ink-muted" />
            <input wire:model.live.debounce.300ms="cari" type="search" placeholder="Cari nomor atau nama pemesan" class="input h-11 bg-surface pl-11">
        </label>
    </x-admin-heading>

    <div class="mt-6 flex gap-2 overflow-x-auto pb-1" role="group" aria-label="Status pesanan">
        <button type="button" wire:click="$set('status', '')" aria-pressed="{{ $aktif ? 'false' : 'true' }}"
            @class(['chip gap-2', 'chip-on' => ! $aktif])>
            Semua <span class="tabular-nums">{{ $jumlah->sum() }}</span>
        </button>
        @foreach (\App\Enums\StatusPesanan::cases() as $status)
            <button type="button" wire:key="status-{{ $status->value }}" wire:click="$set('status', '{{ $status->value }}')"
                aria-pressed="{{ $aktif === $status ? 'true' : 'false' }}"
                @class(['chip gap-2', 'chip-on' => $aktif === $status])>
                {{ $status->label() }} <span class="tabular-nums">{{ $jumlah[$status->value] ?? 0 }}</span>
            </button>
        @endforeach
    </div>

    <div class="table-wrap mt-6">
        <table class="table">
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Pemesan</th>
                    <th>Pesanan</th>
                    <th class="text-right">Total</th>
                    <th>Kurir</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pesanans as $pesanan)
                    <tr wire:key="pesanan-{{ $pesanan->id }}">
                        <td class="whitespace-nowrap">
                            <a wire:navigate href="{{ route('admin.pesanan.detail', $pesanan) }}" class="link tabular-nums">{{ $pesanan->nomor }}</a>
                            <p class="mt-1 text-sm text-ink-muted">@tanggal($pesanan->created_at)</p>
                        </td>
                        <td class="font-medium whitespace-nowrap">{{ $pesanan->user->name }}</td>
                        <td><x-daftar-barang-pesanan :pesanan="$pesanan" /></td>
                        <td class="text-right font-medium whitespace-nowrap tabular-nums">@rupiah($pesanan->total)</td>
                        <td class="whitespace-nowrap">
                            {{ $pesanan->pengiriman?->label() ?? '–' }}
                            @if ($pesanan->pengiriman?->resi)
                                <p class="text-sm text-ink-muted tabular-nums">{{ $pesanan->pengiriman->resi }}</p>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                <x-status-pesanan :status="$pesanan->status" />
                                @if ($pesanan->pembayaran?->status->perluDicek())
                                    <span class="badge {{ $pesanan->pembayaran->status->badge() }}">{{ $pesanan->pembayaran->status->label() }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><x-empty icon="inbox">Tidak ada pesanan dengan status atau kata pencarian ini.</x-empty></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $pesanans->links() }}
    </div>
</div>
