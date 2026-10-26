<div>
    <x-admin-heading title="Selamat Datang, Admin" subtitle="Ringkasan toko hari ini." />

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        <a wire:navigate href="{{ route('admin.pesanan') }}" class="card group flex items-center gap-4 p-6 transition-colors hover:border-line-strong">
            <span class="grid size-12 place-items-center rounded-xs bg-brand-soft text-brand">
                <x-icon name="clipboard-list" class="size-6" />
            </span>
            <div class="flex-1">
                <p class="text-sm text-ink-muted">Pesanan menunggu konfirmasi</p>
                <p class="price mt-0.5 text-3xl">{{ $dataorder }}</p>
            </div>
            <span class="text-sm font-medium text-brand group-hover:underline">Periksa</span>
        </a>
        <div class="card flex items-center gap-4 p-6">
            <span class="grid size-12 place-items-center rounded-xs bg-surface-muted text-ink">
                <x-icon name="users" class="size-6" />
            </span>
            <div>
                <p class="text-sm text-ink-muted">Pelanggan terdaftar</p>
                <p class="price mt-0.5 text-3xl">{{ $user }}</p>
            </div>
        </div>
    </div>

    <h2 class="mt-12 text-xl">Daftar pelanggan</h2>
    <div class="table-wrap mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-16">No.</th>
                    <th>Nama</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengguna as $orang)
                    <tr wire:key="user-{{ $orang->id }}">
                        <td class="text-ink-muted tabular-nums">{{ $loop->iteration }}</td>
                        <td class="font-medium">{{ $orang->name }}</td>
                        <td class="text-ink-muted">{{ $orang->email }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3"><x-empty icon="users">Belum ada pelanggan. Pelanggan yang mendaftar akan muncul di sini.</x-empty></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
