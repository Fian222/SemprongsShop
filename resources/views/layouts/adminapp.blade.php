<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — Semprong</title>

    @include('layouts.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

@php
    $menu = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'Pesanan', 'route' => 'admin.pesanan', 'icon' => 'inbox', 'aktif' => ['admin.pesanan', 'admin.pesanan.*']],
        ['label' => 'Produk', 'route' => 'admin.produk', 'icon' => 'package', 'aktif' => ['admin.produk', 'admin.produk.*']],
        ['label' => 'Kategori', 'route' => 'admin.kategori', 'icon' => 'tag'],
    ];
    $admin = Auth::guard('adminMiddle')->user();
@endphp

<body x-data="{ sidebar: false }" class="bg-surface-muted">
    {{-- latar gelap saat sidebar terbuka di mobile --}}
    <div x-cloak x-show="sidebar" x-transition.opacity @click="sidebar = false"
        class="fixed inset-0 z-40 bg-wine-deep/60 lg:hidden"></div>

    <aside :data-open="sidebar" @keydown.escape.window="sidebar = false"
        class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-wine-deep text-on-wine transition-transform data-open:translate-x-0 lg:translate-x-0">
        <div class="flex h-[76px] items-center gap-2.5 px-6">
            <a wire:navigate href="{{ route('admin.dashboard') }}" class="text-on-wine-strong"><x-logo size="text-[24px]" /></a>
            <span class="rounded-xs border border-champ/40 px-1.5 py-0.5 text-xs text-champ">Admin</span>
        </div>
        <nav class="flex flex-1 flex-col gap-0.5 px-3 py-4" aria-label="Menu admin">
            @foreach ($menu as $item)
                @php($aktif = request()->routeIs(...($item['aktif'] ?? [$item['route']])))
                <a wire:navigate href="{{ route($item['route']) }}" @if ($aktif) aria-current="page" @endif
                    class="relative flex items-center gap-3 rounded-xs px-3 py-2.5 text-[15px] transition-colors
                        {{ $aktif ? 'bg-on-wine-strong/10 text-on-wine-strong before:absolute before:inset-y-2 before:-left-3 before:w-[3px] before:bg-champ' : 'hover:bg-on-wine-strong/5 hover:text-on-wine-strong' }}">
                    <x-icon :name="$item['icon']" class="size-5" /> {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="border-t border-on-wine-strong/10 p-3">
            <p class="truncate px-3 py-2 text-sm text-on-wine-strong">{{ $admin->name }}</p>
            <a href="{{ route('admin.logout') }}"
                class="flex items-center gap-3 rounded-xs px-3 py-2.5 text-[15px] transition-colors hover:bg-on-wine-strong/5 hover:text-on-wine-strong">
                <x-icon name="log-out" class="size-5" /> Keluar
            </a>
        </div>
    </aside>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-line bg-surface px-4 lg:hidden">
            <button type="button" @click="sidebar = true" aria-label="Buka menu" class="grid size-11 place-items-center">
                <x-icon name="menu" class="size-5" />
            </button>
            <x-logo size="text-[22px]" class="text-brand" />
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-8 lg:py-12">
            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>

</html>
