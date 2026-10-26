<div>
    <x-admin-heading title="Riwayat pesanan" subtitle="Semua pesanan beserta statusnya." />

    <div class="table-wrap mt-6">
        <table class="table">
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Pemesan</th>
                    <th>Tanggal</th>
                    <th>Pesanan</th>
                    <th class="text-right">Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pesanan as $item)
                    <tr wire:key="pesanan-{{ $item->id }}">
                        <td class="font-medium whitespace-nowrap tabular-nums">{{ $item->nomor }}</td>
                        <td class="font-medium whitespace-nowrap">{{ $item->user->name }}</td>
                        <td class="whitespace-nowrap text-ink-muted">@tanggal($item->created_at)</td>
                        <td><x-daftar-barang-pesanan :pesanan="$item" /></td>
                        <td class="text-right font-medium whitespace-nowrap tabular-nums">@rupiah($item->total)</td>
                        <td><x-status-pesanan :status="$item->status" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><x-empty icon="history">Belum ada pesanan.</x-empty></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $pesanan->links() }}
    </div>
</div>
