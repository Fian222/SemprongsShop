@extends('layouts.app')

@section('content')
    <x-auth-card title="Selamat datang kembali" subtitle="Masuk untuk melanjutkan pesananmu.">
        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
            @csrf
            <x-field name="email" type="email" label="Email" :value="old('email')" required autocomplete="email" autofocus />

            <div>
                <x-field name="password" type="password" label="Password" required autocomplete="current-password" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="mt-2 inline-block text-sm font-medium text-brand hover:underline">
                        Lupa password?
                    </a>
                @endif
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" class="size-4 rounded accent-brand" @checked(old('remember'))>
                Ingat saya
            </label>

            <button type="submit" class="btn btn-primary w-full">Masuk</button>
        </form>

        <x-slot:footer>
            Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-brand hover:underline">Daftar sekarang</a>
        </x-slot:footer>
    </x-auth-card>
@endsection
