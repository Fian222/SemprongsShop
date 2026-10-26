<div>
    <x-admin-heading title="Pesanan masuk" subtitle="Pesanan yang sudah dibayar dan perlu disiapkan." />

    <div class="table-wrap mt-6">
        <table class="table">
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Pemesan</th>
                    <th>Dibayar</th>
                    <th>Pesanan</th>
                    <th class="text-right">Total</th>
                    <th>Dikirim ke</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pesanan as $item)
                    <tr wire:key="pesanan-{{ $item->id }}">
                        <td class="font-medium whitespace-nowrap tabular-nums">{{ $item->nomor }}</td>
                        <td class="font-medium whitespace-nowrap">{{ $item->user->name }}</td>
                        <td class="whitespace-nowrap text-ink-muted">@tanggal($item->dibayar_at)</td>
                        <td><x-daftar-barang-pesanan :pesanan="$item" /></td>
                        <td class="text-right font-medium whitespace-nowrap tabular-nums">@rupiah($item->total)</td>
                        <td class="min-w-56">
                            <p class="font-medium">{{ $item->alamat['penerima'] }} · {{ $item->alamat['no_hp'] }}</p>
                            <p class="text-ink-muted">{{ $item->alamat['alamat'] }}, {{ $item->alamat['kode_pos'] }}</p>
                            @if ($item->pengiriman)
                                <p class="mt-1 font-medium">{{ $item->pengiriman->label() }}</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><x-empty>Belum ada pesanan dibayar yang perlu disiapkan. Pesanan muncul di sini begitu pembayarannya diterima.</x-empty></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
