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
                <input type="search" name="search" value="{{ $rute === 'produk' ? request('search') : '' }}" placeholder="Cari rasa"
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

            <a wire:navigate href="{{ route('pesanann') }}" @if ($aktif('pesanann')) aria-current="page" @endif
                aria-label="Tas belanja, {{ $jumlah_pesanan }} barang"
                class="flex h-10 items-center gap-2 rounded-xs border border-ink px-3.5 text-sm whitespace-nowrap transition-colors hover:bg-ink hover:text-canvas">
                <x-icon name="shopping-bag" /> Tas ({{ $jumlah_pesanan }})
            </a>

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
                <input type="search" name="search" placeholder="Cari rasa" aria-label="Cari produk" class="input pl-11">
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
