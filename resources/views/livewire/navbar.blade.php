@php
    $menu = [
        ['label' => 'Semprong', 'route' => 'produk'],
        ['label' => 'Riwayat pesanan', 'route' => 'history'],
    ];
    $aktif = fn ($route) => $rute === $route;
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-line bg-canvas">
    <nav class="wrap flex h-[76px] items-center gap-8" aria-label="Utama">
        <a wire:navigate href="{{ route('home') }}" aria-label="Semprong, beranda" class="text-brand">
            <x-logo />
        </a>

        <ul class="hidden items-center gap-7 text-[15px] md:flex">
            @foreach ($menu as $item)
                <li>
                    <a wire:navigate href="{{ route($item['route']) }}" @if ($aktif($item['route'])) aria-current="page" @endif
                        class="border-b py-1 transition-colors {{ $aktif($item['route']) ? 'border-gold text-ink' : 'border-transparent text-ink-muted hover:text-ink' }}">{{ $item['label'] }}</a>
                </li>
            @endforeach
        </ul>

        <div class="ml-auto flex items-center gap-2">
            <form action="{{ route('produk') }}" role="search" class="relative hidden lg:block">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-0 size-[18px] -translate-y-1/2 text-ink-muted" />
                <input type="search" name="cari" value="{{ $rute === 'produk' ? request('cari') : '' }}" placeholder="Cari rasa"
                    aria-label="Cari produk" class="h-10 w-44 border-b border-line-strong bg-transparent pl-7 text-[15px] placeholder:text-ink-muted/80 focus:border-ink focus:outline-2 focus:outline-offset-2 focus:outline-gold">
            </form>

            @guest
                <a href="{{ route('login') }}" class="hidden px-3 py-2.5 text-[15px] text-ink-muted hover:text-ink md:block">Masuk</a>
            @else
                <div x-data="{ akun: false }" class="relative hidden md:block" @click.outside="akun = false" @keydown.escape="akun = false">
                    <button type="button" @click="akun = !akun" :aria-expanded="akun"
                        class="flex h-11 items-center gap-1.5 px-3 text-[15px] text-ink-muted hover:text-ink">
                        {{ Auth::user()->name }} <x-icon name="chevron-down" />
                    </button>
                    <div x-cloak x-show="akun" x-transition.origin.top.right
                        class="card absolute right-0 mt-1 w-52 p-1 shadow-lg shadow-ink/10">
                        <a wire:navigate href="{{ route('history') }}"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-ink-muted hover:bg-surface-muted hover:text-ink">
                            <x-icon name="history" /> Riwayat pesanan
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-ink-muted hover:bg-surface-muted hover:text-ink">
                                <x-icon name="log-out" /> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @endguest

            {{-- ringkasan keranjang; terbuka sendiri saat varian baru masuk dari halaman produk --}}
            <div x-data="{ ringkasan: false }" class="relative" @click.outside="ringkasan = false" @keydown.escape="ringkasan = false"
                @keranjang-diperbarui.window="if ($event.detail.buka) ringkasan = true">
                <button type="button" @click="ringkasan = !ringkasan" :aria-expanded="ringkasan" aria-controls="ringkasan-keranjang"
                    aria-label="Tas belanja, {{ $jumlahBarang }} barang"
                    @class(['flex h-10 items-center gap-2 rounded-xs border border-ink px-3.5 text-sm whitespace-nowrap transition-colors hover:bg-ink hover:text-canvas',
                        'bg-ink text-canvas' => $aktif('keranjang')])>
                    <x-icon name="shopping-bag" /> Tas ({{ $jumlahBarang }})
                </button>
                <div id="ringkasan-keranjang" x-cloak x-show="ringkasan" x-transition.origin.top.right
                    class="card absolute right-0 z-50 mt-2 w-88 shadow-lg shadow-ink/10 max-sm:fixed max-sm:inset-x-4 max-sm:top-21 max-sm:mt-0 max-sm:w-auto">
                    @if ($jumlahBarang)
                        <ul class="max-h-80 divide-y divide-line overflow-y-auto">
                            @foreach ($keranjang->items as $item)
                                <li wire:key="mini-{{ $item->id }}" class="flex items-center gap-3 px-4 py-3">
                                    <x-foto-produk :foto="$item->varian->produk->fotoUtama" class="size-12 shrink-0 rounded-xs bg-surface-muted" />
                                    <div class="min-w-0 flex-1 text-sm">
                                        <p class="truncate font-medium">{{ $item->varian->produk->nama }}</p>
                                        <p class="text-ink-muted tabular-nums">{{ $item->varian->nama }} · {{ $item->jumlah }} ×</p>
                                    </div>
                                    <p class="price text-sm">@rupiah($item->jumlahHarga())</p>
                                </li>
                            @endforeach
                        </ul>
                        <div class="border-t border-line p-4">
                            <p class="flex items-baseline justify-between">
                                <span class="text-sm text-ink-muted">Subtotal</span>
                                <span class="price text-lg">@rupiah($keranjang->subtotal())</span>
                            </p>
                            <a wire:navigate href="{{ route('keranjang') }}" class="btn btn-primary mt-3 w-full">Lihat tas belanja</a>
                        </div>
                    @else
                        <p class="px-4 py-6 text-center text-sm text-ink-muted">Tas belanjamu masih kosong.</p>
                    @endif
                </div>
            </div>

            <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="menu-hp" aria-label="Menu"
                class="grid size-11 place-items-center text-ink md:hidden">
                <x-icon name="menu" class="size-5" x-show="!open" />
                <x-icon name="x" class="size-5" x-cloak x-show="open" />
            </button>
        </div>
    </nav>

    <div id="menu-hp" x-cloak x-show="open" x-transition.opacity class="border-t border-line md:hidden">
        <div class="wrap flex flex-col gap-1 py-4">
            <form action="{{ route('produk') }}" role="search" class="relative mb-2">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-ink-muted" />
                <input type="search" name="cari" placeholder="Cari rasa" aria-label="Cari produk" class="input pl-11">
            </form>
            @foreach ($menu as $item)
                <a wire:navigate href="{{ route($item['route']) }}"
                    class="py-2.5 text-[17px] {{ $aktif($item['route']) ? 'text-ink' : 'text-ink-muted' }}">{{ $item['label'] }}</a>
            @endforeach
            <div class="mt-2 flex gap-2 border-t border-line pt-4">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-secondary flex-1">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary flex-1">Daftar</a>
                @else
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="btn btn-secondary w-full">
                            <x-icon name="log-out" /> Keluar ({{ Auth::user()->name }})
                        </button>
                    </form>
                @endguest
            </div>
        </div>
    </div>
</header>
