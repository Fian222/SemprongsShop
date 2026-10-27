<div>
    <x-admin-heading title="Voucher" subtitle="Kode promo yang memotong subtotal belanja. Pemakaian dihitung dari pesanan yang sudah dibayar." />

    <x-flash class="mt-6" />
    @error('hapus') <x-alert type="danger" class="mt-6">{{ $message }}</x-alert> @enderror

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Voucher</th>
                        <th>Berlaku</th>
                        <th class="text-right">Dipakai</th>
                        <th><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vouchers as $voucher)
                        @php([$keadaan, $badge] = $voucher->keadaan())
                        <tr wire:key="voucher-{{ $voucher->id }}">
                            <td>
                                <p class="flex items-center gap-2 font-medium tracking-wide">{{ $voucher->kode }} <span class="badge {{ $badge }}">{{ $keadaan }}</span></p>
                                <p class="text-sm text-ink-muted">{{ $voucher->ringkasan() }}</p>
                                @if ($voucher->min_subtotal)
                                    <p class="text-sm text-ink-muted">Min. belanja @rupiah($voucher->min_subtotal)</p>
                                @endif
                            </td>
                            <td class="text-sm text-ink-muted">
                                @if ($voucher->mulai)
                                    <p>Mulai @tanggal($voucher->mulai)</p>
                                @endif
                                @if ($voucher->berakhir)
                                    <p>Sampai @tanggal($voucher->berakhir)</p>
                                @endif
                                @if (! $voucher->mulai && ! $voucher->berakhir)
                                    <p>Tanpa batas waktu</p>
                                @endif
                            </td>
                            <td class="text-right">
                                <p class="tabular-nums">{{ $voucher->terpakai }}{{ $voucher->kuota !== null ? ' / '.$voucher->kuota : '' }}</p>
                                @if ($voucher->menunggu)
                                    <p class="text-sm text-ink-muted">{{ $voucher->menunggu }} menunggu pembayaran</p>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <button type="button" wire:click="ubah({{ $voucher->id }})" class="btn btn-secondary btn-icon" aria-label="Ubah voucher {{ $voucher->kode }}">
                                    <x-icon name="pencil" class="size-[18px]" />
                                </button>
                                <button type="button" wire:click="hapus({{ $voucher->id }})" wire:confirm="Hapus voucher {{ $voucher->kode }}?"
                                    class="btn btn-danger btn-icon" aria-label="Hapus voucher {{ $voucher->kode }}">
                                    <x-icon name="trash" class="size-[18px]" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4"><x-empty icon="ticket">Belum ada voucher. Buat yang pertama lewat formulir di samping.</x-empty></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form wire:submit="simpan" class="card flex flex-col gap-5 p-6 lg:sticky lg:top-8">
            <h2 class="text-xl">{{ $form->voucher ? 'Ubah voucher' : 'Tambah voucher' }}</h2>
            <div>
                <label for="voucher-kode" class="label">Kode</label>
                <input id="voucher-kode" type="text" wire:model="form.kode" required maxlength="30" autocapitalize="characters" spellcheck="false"
                    placeholder="mis. HEMAT10" class="input uppercase placeholder:normal-case @error('form.kode') input-invalid @enderror">
                @error('form.kode') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="voucher-tipe" class="label">Tipe</label>
                    <select id="voucher-tipe" wire:model.live="form.tipe" class="input @error('form.tipe') input-invalid @enderror">
                        @foreach (\App\Enums\TipeVoucher::cases() as $tipe)
                            <option value="{{ $tipe->value }}">{{ $tipe->label() }}</option>
                        @endforeach
                    </select>
                    @error('form.tipe') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="voucher-nilai" class="label">{{ $form->persen() ? 'Diskon (%)' : 'Diskon (Rp)' }}</label>
                    <input id="voucher-nilai" type="number" min="1" @if ($form->persen()) max="100" @endif wire:model="form.nilai" required
                        class="input @error('form.nilai') input-invalid @enderror">
                    @error('form.nilai') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
            @if ($form->persen())
                <div>
                    <label for="voucher-maks" class="label">Maksimal diskon (Rp, opsional)</label>
                    <input id="voucher-maks" type="number" min="1" wire:model="form.maks_diskon" class="input @error('form.maks_diskon') input-invalid @enderror">
                    @error('form.maks_diskon') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            @endif
            <div>
                <label for="voucher-min" class="label">Minimum subtotal (Rp)</label>
                <input id="voucher-min" type="number" min="0" wire:model="form.min_subtotal" required aria-describedby="voucher-min-petunjuk"
                    class="input @error('form.min_subtotal') input-invalid @enderror">
                <p id="voucher-min-petunjuk" class="hint">Isi 0 bila voucher berlaku untuk belanja berapa pun.</p>
                @error('form.min_subtotal') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="voucher-kuota" class="label">Kuota (opsional)</label>
                <input id="voucher-kuota" type="number" min="1" wire:model="form.kuota" aria-describedby="voucher-kuota-petunjuk"
                    class="input @error('form.kuota') input-invalid @enderror">
                <p id="voucher-kuota-petunjuk" class="hint">Jumlah pesanan yang boleh memakainya. Kosongkan untuk tanpa batas.</p>
                @error('form.kuota') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <div>
                    <label for="voucher-mulai" class="label">Mulai (opsional)</label>
                    <input id="voucher-mulai" type="datetime-local" wire:model="form.mulai" class="input @error('form.mulai') input-invalid @enderror">
                    @error('form.mulai') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="voucher-berakhir" class="label">Berakhir (opsional)</label>
                    <input id="voucher-berakhir" type="datetime-local" wire:model="form.berakhir" class="input @error('form.berakhir') input-invalid @enderror">
                    @error('form.berakhir') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <label class="flex items-center gap-2.5 text-[15px]">
                <input type="checkbox" wire:model="form.aktif" class="size-4 accent-brand"> Aktif
            </label>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <x-icon name="check" /> Simpan
                </button>
                @if ($form->voucher)
                    <button type="button" wire:click="batal" class="btn btn-secondary">Batal</button>
                @endif
            </div>
        </form>
    </div>
</div>
