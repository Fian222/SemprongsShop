@extends('layouts.app')

@section('content')
    <div class="wrap max-w-xl pt-10">
        <div class="card p-6 sm:p-8">
            <p class="badge badge-warn">Mode simulasi</p>
            <h1 class="mt-4 text-3xl">Simulasi pembayaran</h1>
            <p class="mt-2 text-ink-muted">
                Halaman ini menggantikan jendela Midtrans selama pengembangan. Pilih hasil pembayaran untuk pesanan
                <b class="font-medium text-ink tabular-nums">{{ $pesanan->nomor }}</b>.
            </p>
            <p class="price mt-5 text-3xl">@rupiah($pesanan->total)</p>

            <form method="POST" class="mt-6 grid gap-2 sm:grid-cols-2">
                @csrf
                <button name="status" value="settlement" class="btn btn-primary sm:col-span-2">Bayar berhasil</button>
                <button name="status" value="expire" class="btn btn-secondary">Kedaluwarsa</button>
                <button name="status" value="cancel" class="btn btn-secondary">Batalkan</button>
            </form>
            <a href="{{ route('pesanan.detail', $pesanan) }}" class="link mt-5 inline-block text-sm">Kembali tanpa membayar</a>
        </div>
    </div>
@endsection
