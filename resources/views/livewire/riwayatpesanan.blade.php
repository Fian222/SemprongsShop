<div>
    <x-admin-heading title="Riwayat pesanan" subtitle="Pesanan yang pembayarannya sudah dikonfirmasi." />

    <div class="table-wrap mt-6">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Pemesan</th>
                    <th>Tanggal</th>
                    <th>Pesanan</th>
                    <th class="text-right">Total</th>
                    <th>Kontak &amp; alamat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pesanan as $item)
                    <tr wire:key="pesanan-{{ $item->id }}">
                        <td class="font-medium whitespace-nowrap tabular-nums">{{ $item->kode_pemesanan }}</td>
                        <td class="font-medium whitespace-nowrap">{{ $item->user->name }}</td>
                        <td class="whitespace-nowrap text-ink-muted">@tanggal($item->created_at)</td>
                        <td><x-daftar-barang-pesanan :pesanan="$item" /></td>
                        <td class="text-right font-medium whitespace-nowrap tabular-nums">@rupiah($item->total_harga)</td>
                        <td class="min-w-56">
                            <p class="font-medium">{{ $item->user->notelp }}</p>
                            <p class="text-ink-muted">{{ $item->user->alamat }}</p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><x-empty icon="history">Belum ada pesanan yang dikonfirmasi. Konfirmasi pesanan dari halaman Pesanan masuk.</x-empty></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
