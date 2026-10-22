@extends('layouts.app')

@section('content')
    <x-auth-card title="Buat akun" subtitle="Daftar sekali, pesan semprong kapan saja.">
        <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-5">
            @csrf
            <x-field name="name" label="Nama" :value="old('name')" required autocomplete="name" autofocus />
            <x-field name="email" type="email" label="Email" :value="old('email')" required autocomplete="email" />
            <x-field name="password" type="password" label="Password" required autocomplete="new-password" />
            <x-field name="password_confirmation" type="password" label="Ulangi password" required autocomplete="new-password" />

            <button type="submit" class="btn btn-primary w-full">Daftar</button>
        </form>

        <x-slot:footer>
            Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">Masuk</a>
        </x-slot:footer>
    </x-auth-card>
@endsection
