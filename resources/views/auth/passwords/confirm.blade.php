@extends('layouts.app')

@section('content')
    <x-auth-card title="Konfirmasi password" subtitle="Demi keamanan, masukkan lagi passwordmu sebelum melanjutkan.">
        <form method="POST" action="{{ route('password.confirm') }}" class="flex flex-col gap-5">
            @csrf
            <x-field name="password" type="password" label="Password" required autocomplete="current-password" />
            <button type="submit" class="btn btn-primary w-full">Konfirmasi</button>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-center text-sm font-medium text-brand hover:underline">Lupa password?</a>
            @endif
        </form>
    </x-auth-card>
@endsection
