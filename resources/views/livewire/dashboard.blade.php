<div>
    <x-admin-heading title="Selamat Datang, Admin" subtitle="Ringkasan penjualan toko. Omzet dihitung dari pesanan yang sudah dibayar, tanpa ongkir." />

    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <div class="card p-6">
            <p class="text-sm text-ink-muted">Omzet hari ini</p>
            <p class="price mt-1 text-3xl">@rupiah($omzetHariIni)</p>
        </div>
        <div class="card p-6">
            <p class="text-sm text-ink-muted">Omzet 30 hari terakhir</p>
            <p class="price mt-1 text-3xl">@rupiah($omzet30Hari)</p>
        </div>
        <div class="card p-6">
            <p class="text-sm text-ink-muted">Pelanggan terdaftar</p>
            <p class="price mt-1 text-3xl">{{ $jumlahPelanggan }}</p>
        </div>
    </div>

    <section class="card mt-4 p-6" aria-labelledby="judul-harian">
        <h2 id="judul-harian" class="text-xl">Omzet harian</h2>
        <p class="mt-1 text-sm text-ink-muted">30 hari terakhir, menurut hari pembayaran diterima.</p>
        <x-grafik-omzet :harian="$omzetHarian" class="mt-6" />
    </section>

    <section class="mt-10" aria-labelledby="judul-status">
        <h2 id="judul-status" class="text-xl">Pesanan per status</h2>
        <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
            @foreach (\App\Enums\StatusPesanan::cases() as $status)
                <li wire:key="status-{{ $status->value }}">
                    <a wire:navigate href="{{ route('admin.pesanan', ['status' => $status->value]) }}"
                        class="card flex h-full flex-col items-start gap-3 p-4 transition-colors hover:border-line-strong">
                        <x-status-pesanan :status="$status" />
                        <span class="price text-2xl">{{ $jumlahPerStatus[$status->value] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <div class="mt-10 grid gap-10 lg:grid-cols-2 lg:gap-6">
        <section aria-labelledby="judul-terlaris">
            <h2 id="judul-terlaris" class="text-xl">Terlaris 30 hari terakhir</h2>
            <p class="mt-1 text-sm text-ink-muted">Jumlah barang terjual dari pesanan yang sudah dibayar.</p>
            <div class="card mt-4">
                @if ($terlaris->isEmpty())
                    <x-empty icon="package">Belum ada pesanan yang dibayar dalam 30 hari terakhir.</x-empty>
                @else
                    <ol class="divide-y divide-line">
                        @foreach ($terlaris as $produk)
                            <li class="flex items-center gap-4 px-5 py-3.5">
                                <span class="w-5 text-ink-muted tabular-nums">{{ $loop->iteration }}</span>
                                <span class="flex-1 font-medium">{{ $produk->nama_produk }}</span>
                                <span class="text-sm whitespace-nowrap text-ink-muted tabular-nums">{{ $produk->terjual }} terjual</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>

        <section aria-labelledby="judul-stok">
            <h2 id="judul-stok" class="text-xl">Stok menipis</h2>
            <p class="mt-1 text-sm text-ink-muted">Varian aktif yang stoknya tinggal di bawah {{ \App\Models\Varian::STOK_MENIPIS }}.</p>
            <div class="table-wrap mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Varian</th>
                            <th class="text-right">Stok</th>
                            <th><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stokMenipis as $varian)
                            <tr wire:key="menipis-{{ $varian->id }}">
                                <td>
                                    <p class="font-medium">{{ $varian->produk->nama }}</p>
                                    <p class="text-sm text-ink-muted">{{ $varian->nama }} · {{ $varian->sku }}</p>
                                </td>
                                <td class="text-right"><span class="badge badge-low">{{ $varian->stok }}</span></td>
                                <td class="text-right">
                                    {{-- tanpa wire:navigate supaya browser menggulir ke #judul-varian --}}
                                    <a href="{{ route('admin.produk.ubah', ['produk' => $varian->produk_id, 'varian' => $varian->id]) }}#judul-varian"
                                        class="btn btn-secondary btn-sm" aria-label="Ubah varian {{ $varian->nama }} {{ $varian->produk->nama }}">
                                        <x-icon name="pencil" /> Ubah
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3"><x-empty icon="package">Semua varian aktif stoknya aman.</x-empty></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="card mt-10 p-6" aria-labelledby="judul-ekspor">
        <h2 id="judul-ekspor" class="text-xl">Ekspor pesanan</h2>
        <p class="mt-1 text-sm text-ink-muted">Unduh pesanan semua status yang dibuat pada rentang tanggal ini sebagai CSV untuk Excel atau Google Sheets.</p>
        <form wire:submit="ekspor" class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-start">
            <div class="sm:w-52">
                <label for="ekspor-dari" class="label">Dari tanggal</label>
                <input id="ekspor-dari" type="date" wire:model="dari" required
                    class="input @error('dari') input-invalid @enderror">
                @error('dari') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="sm:w-52">
                <label for="ekspor-sampai" class="label">Sampai tanggal</label>
                <input id="ekspor-sampai" type="date" wire:model="sampai" required
                    class="input @error('sampai') input-invalid @enderror">
                @error('sampai') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn btn-secondary sm:mt-7" wire:loading.attr="disabled">
                <x-icon name="download" /> Unduh CSV
            </button>
        </form>
    </section>
</div>
