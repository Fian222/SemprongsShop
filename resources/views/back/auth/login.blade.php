<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Admin — Semprong Shop</title>

    @include('layouts.fonts')
    @vite(['resources/css/app.css'])
</head>

<body class="grid min-h-dvh place-items-center bg-surface-muted px-4 py-12">
    <main class="w-full max-w-md">
        <p class="text-center">
            <x-logo size="text-[30px]" class="text-brand" />
        </p>
        <div class="card mt-6 p-6 sm:p-8">
            <h1 class="text-4xl leading-tight">Login admin</h1>
            <p class="mt-2 text-ink-muted">Kelola produk dan pesanan toko.</p>

            @if (session()->has('message'))
                <x-alert class="mt-6">{{ session('message') }}</x-alert>
            @endif

            <form method="POST" novalidate action="{{ route('admin.login') }}" class="mt-8 flex flex-col gap-5">
                @csrf
                <x-field name="email" type="email" label="Email" :value="old('email')" autocomplete="username" autofocus />
                <x-field name="password" type="password" label="Password" required autocomplete="current-password" />
                <button type="submit" class="btn btn-primary w-full">Masuk</button>
            </form>
        </div>
    </main>
</body>

</html>
