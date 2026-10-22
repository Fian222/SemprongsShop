@extends('layouts.app')

@section('content')
    <x-auth-card title="Buat password baru">
        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-field name="email" type="email" label="Email" :value="$email ?? old('email')" required autocomplete="email" autofocus />
            <x-field name="password" type="password" label="Password baru" required autocomplete="new-password" />
            <x-field name="password_confirmation" type="password" label="Ulangi password baru" required autocomplete="new-password" />
            <button type="submit" class="btn btn-primary w-full">Simpan password</button>
        </form>
    </x-auth-card>
@endsection
