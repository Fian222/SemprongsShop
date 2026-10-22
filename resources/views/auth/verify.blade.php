@extends('layouts.app')

@section('content')
    <x-auth-card title="Verifikasi email" subtitle="Cek kotak masuk emailmu untuk tautan verifikasi sebelum melanjutkan.">
        @if (session('resent'))
            <x-alert class="mb-5">Tautan verifikasi baru sudah dikirim ke emailmu.</x-alert>
        @endif

        <form method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="btn btn-secondary w-full">Kirim ulang tautan verifikasi</button>
        </form>
    </x-auth-card>
@endsection
