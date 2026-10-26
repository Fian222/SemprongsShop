<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#fbf6ee" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#17110d" media="(prefers-color-scheme: dark)">

    <title>Semprong Shop — Kue semprong renyah buatan rumah</title>

    @include('layouts.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

{{-- PROTOTYPE #11 (branch prototype/premium): varian redesign membawa header & footer sendiri --}}
@php($protoVariant = request()->routeIs('home') && in_array(request('variant'), ['A', 'B', 'C']) ? request('variant') : null)

<body class="flex min-h-dvh flex-col overflow-x-clip">
    @unless ($protoVariant)
        <livewire:navbar />
    @endunless

    <main class="flex-1 pb-16">
        @yield('content')
    </main>

    @unless ($protoVariant)
        @include('layouts.footer')
    @endunless

    <x-prototype-switcher />

    @livewireScripts
</body>

</html>
