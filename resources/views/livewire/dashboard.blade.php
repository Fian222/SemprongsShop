<div>
    <x-admin-heading title="Selamat Datang, Admin" subtitle="Ringkasan toko hari ini." />

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        <a wire:navigate href="{{ route('admin.pesanan') }}" class="card group flex items-center gap-5 p-6 transition hover:border-brand/40">
            <span class="grid size-14 place-items-center rounded-2xl bg-brand-soft text-brand">
                <x-icon name="clipboard-list" class="size-7" />
            </span>
            <div>
                <p class="text-sm font-medium text-ink-muted">Pesanan menunggu konfirmasi</p>
                <p class="font-display text-4xl font-bold">{{ $dataorder }}</p>
            </div>
            <x-icon name="arrow-right" class="ml-auto size-5 text-ink-muted transition group-hover:translate-x-1 group-hover:text-brand" />
        </a>
        <div class="card flex items-center gap-5 p-6">
            <span class="grid size-14 place-items-center rounded-2xl bg-ok-soft text-ok">
                <x-icon name="users" class="size-7" />
            </span>
            <div>
                <p class="text-sm font-medium text-ink-muted">Pelanggan terdaftar</p>
                <p class="font-display text-4xl font-bold">{{ $user }}</p>
            </div>
        </div>
    </div>

    <h2 class="mt-12 text-2xl font-bold">Daftar pelanggan</h2>
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
                        <td class="text-ink-muted">{{ $loop->iteration }}</td>
                        <td class="font-medium">{{ $orang->name }}</td>
                        <td class="text-ink-muted">{{ $orang->email }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3"><x-empty icon="users">Belum ada pelanggan.</x-empty></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
