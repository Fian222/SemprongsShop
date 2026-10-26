<div>
    <x-admin-heading title="Pesanan masuk" subtitle="Pesanan yang sudah checkout dan menunggu konfirmasi pembayaran." />

    <x-flash class="mt-6" />

    <div class="table-wrap mt-6">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Pemesan</th>
                    <th>Tanggal</th>
                    <th>Pesanan</th>
                    <th class="text-right">Total</th>
                    <th></th>
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
                        <td class="text-right">
                            <button type="button" wire:click="konfirmasi({{ $item->id }})"
                                class="btn btn-primary btn-sm whitespace-nowrap">
                                <x-icon name="check" /> Konfirmasi
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><x-empty>Tidak ada pesanan yang menunggu konfirmasi. Pesanan baru muncul di sini setelah pelanggan checkout.</x-empty></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
