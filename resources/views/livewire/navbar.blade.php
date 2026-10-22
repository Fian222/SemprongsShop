@php
    $menu = [
        ['label' => 'Beranda', 'route' => 'home'],
        ['label' => 'Produk', 'route' => 'produk'],
        ['label' => 'Riwayat', 'route' => 'history'],
    ];
    $tautan = fn ($route) => request()->routeIs($route)
        ? 'text-brand bg-brand-soft'
        : 'text-ink-muted hover:text-ink hover:bg-surface-muted';
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-line bg-canvas/85 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6">
        <a wire:navigate href="{{ route('home') }}" class="font-display text-xl font-bold">
            Semprong<span class="text-brand">Loves</span>
        </a>

        <ul class="ml-6 hidden items-center gap-1 md:flex">
            @foreach ($menu as $item)
                <li>
                    <a wire:navigate href="{{ route($item['route']) }}"
                        class="rounded-full px-3.5 py-2 text-sm font-medium transition {{ $tautan($item['route']) }}">{{ $item['label'] }}</a>
                </li>
            @endforeach
        </ul>

        <div class="ml-auto flex items-center gap-2">
            <a wire:navigate href="{{ route('pesanann') }}" aria-label="Pesanan"
                class="relative grid size-10 place-items-center rounded-full transition {{ $tautan('pesanann') }}">
                <x-icon name="shopping-bag" class="size-5" />
                @if ($jumlah_pesanan !== 0)
                    <span class="absolute -top-0.5 -right-0.5 grid min-w-5 place-items-center rounded-full bg-brand px-1 text-[11px] font-bold text-on-brand">
                        {{ $jumlah_pesanan }}
                    </span>
                @endif
            </a>

            @guest
                <div class="hidden items-center gap-2 md:flex">
                    <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
                </div>
            @else
                <div x-data="{ akun: false }" class="relative hidden md:block" @click.outside="akun = false">
                    <button type="button" @click="akun = !akun" :aria-expanded="akun"
                        class="flex items-center gap-2 rounded-full border border-line bg-surface py-1.5 pr-3 pl-1.5 text-sm font-medium transition hover:bg-surface-muted">
                        <span class="grid size-7 place-items-center rounded-full bg-brand-soft text-xs font-bold text-brand">
                            {{ str(Auth::user()->name)->substr(0, 1)->upper() }}
                        </span>
                        {{ Auth::user()->name }}
                        <x-icon name="chevron-down" class="text-ink-muted" />
                    </button>
                    <div x-cloak x-show="akun" x-transition.origin.top.right
                        class="card absolute right-0 mt-2 w-48 overflow-hidden p-1 shadow-lg">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm text-ink-muted transition hover:bg-surface-muted hover:text-ink">
                                <x-icon name="log-out" /> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @endguest

            <button type="button" @click="open = !open" :aria-expanded="open" aria-label="Buka menu"
                class="grid size-10 place-items-center rounded-full text-ink-muted transition hover:bg-surface-muted md:hidden">
                <x-icon name="menu" class="size-5" x-show="!open" />
                <x-icon name="x" class="size-5" x-cloak x-show="open" />
            </button>
        </div>
    </nav>

    <div x-cloak x-show="open" x-transition class="border-t border-line px-4 pb-4 md:hidden">
        <ul class="flex flex-col gap-1 pt-3">
            @foreach ($menu as $item)
                <li>
                    <a wire:navigate href="{{ route($item['route']) }}"
                        class="block rounded-xl px-3 py-2.5 text-sm font-medium {{ $tautan($item['route']) }}">{{ $item['label'] }}</a>
                </li>
            @endforeach
        </ul>
        <div class="mt-3 flex gap-2 border-t border-line pt-3">
            @guest
                <a href="{{ route('login') }}" class="btn btn-secondary btn-sm flex-1">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm flex-1">Daftar</a>
            @else
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm w-full">
                        <x-icon name="log-out" /> Keluar ({{ Auth::user()->name }})
                    </button>
                </form>
            @endguest
        </div>
    </div>
</header>
