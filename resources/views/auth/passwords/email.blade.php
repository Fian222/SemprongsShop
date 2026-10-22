@extends('layouts.app')

@section('content')
    <x-auth-card title="Lupa password" subtitle="Masukkan emailmu, kami kirimkan tautan untuk membuat password baru.">
        @if (session('status'))
            <x-alert class="mb-5">{{ session('status') }}</x-alert>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
            @csrf
            <x-field name="email" type="email" label="Email" :value="old('email')" required autocomplete="email" autofocus />
            <button type="submit" class="btn btn-primary w-full">Kirim tautan reset</button>
        </form>

        <x-slot:footer>
            Ingat password? <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">Masuk</a>
        </x-slot:footer>
    </x-auth-card>
@endsection
