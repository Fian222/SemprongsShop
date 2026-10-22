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

<body class="flex min-h-dvh flex-col overflow-x-clip">
    <livewire:navbar />

    <main class="flex-1 pb-16">
        @yield('content')
    </main>

    @include('layouts.footer')

    @livewireScripts
</body>

</html>
