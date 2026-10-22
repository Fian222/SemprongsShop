<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — Semprong Shop</title>

    @include('layouts.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

@php
    $menu = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'Pesanan masuk', 'route' => 'admin.pesanan', 'icon' => 'inbox'],
        ['label' => 'Riwayat pesanan', 'route' => 'admin.riwayat', 'icon' => 'history'],
        ['label' => 'Produk', 'route' => 'admin.produk', 'icon' => 'package', 'aktif' => ['admin.produk', 'admin.detail', 'admin.tambah']],
    ];
@endphp

<body x-data="{ sidebar: false }">
    {{-- latar gelap saat sidebar terbuka di mobile --}}
    <div x-cloak x-show="sidebar" x-transition.opacity @click="sidebar = false"
        class="fixed inset-0 z-40 bg-black/40 lg:hidden"></div>

    <aside :data-open="sidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-line bg-surface transition-transform data-open:translate-x-0 lg:translate-x-0">
        <div class="flex h-16 items-center px-6">
            <a wire:navigate href="{{ route('admin.dashboard') }}" class="font-display text-xl font-bold">
                <x-logo />
            </a>
            <span class="badge ml-2 bg-surface-muted text-ink-muted">Admin</span>
        </div>
        <nav class="flex flex-1 flex-col gap-1 px-3 py-4">
            @foreach ($menu as $item)
                @php($aktif = request()->routeIs(...($item['aktif'] ?? [$item['route']])))
                <a wire:navigate href="{{ route($item['route']) }}" @if ($aktif) aria-current="page" @endif
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                        {{ $aktif ? 'bg-brand-soft text-brand' : 'text-ink-muted hover:bg-surface-muted hover:text-ink' }}">
                    <x-icon :name="$item['icon']" class="size-5" /> {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <a href="{{ route('admin.logout') }}"
            class="m-3 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-ink-muted transition hover:bg-bad-soft hover:text-bad">
            <x-icon name="log-out" class="size-5" /> Keluar
        </a>
    </aside>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-line bg-canvas/85 px-4 backdrop-blur sm:px-8">
            <button type="button" @click="sidebar = true" aria-label="Buka menu"
                class="grid size-10 place-items-center rounded-full text-ink-muted transition hover:bg-surface-muted lg:hidden">
                <x-icon name="menu" class="size-5" />
            </button>
            <div class="ml-auto flex items-center gap-2 text-sm font-medium">
                <span class="grid size-8 place-items-center rounded-full bg-brand-soft text-xs font-bold text-brand">
                    {{ str(Auth::guard('adminMiddle')->user()->name)->substr(0, 1)->upper() }}
                </span>
                {{ Auth::guard('adminMiddle')->user()->name }}
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-8">
            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>

</html>
