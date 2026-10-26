<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#171012" media="(prefers-color-scheme: dark)">

    <title>Semprong Shop — Semprong untuk dinikmati dan dihadiahkan</title>

    @include('layouts.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="flex min-h-dvh flex-col overflow-x-clip">
    <livewire:navbar />

    <main class="flex-1 pb-20">
        @yield('content')
    </main>

    @include('layouts.footer')

    @livewireScripts
</body>

</html>
