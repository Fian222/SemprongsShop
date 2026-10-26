<footer class="border-t border-line text-sm text-ink-muted">
    <div class="wrap grid gap-8 py-14 md:grid-cols-[2fr_1fr_1fr_1fr]">
        <div>
            <a wire:navigate href="{{ route('home') }}" class="text-brand"><x-logo /></a>
            <p class="mt-3 max-w-xs leading-relaxed">Kue gulung buatan rumah untuk dinikmati dan dihadiahkan.</p>
        </div>
        <div>
            <h2 class="font-sans text-[15px] font-medium tracking-normal text-ink">Belanja</h2>
            <ul class="mt-3 flex flex-col gap-2">
                <li><a wire:navigate href="{{ route('produk') }}" class="hover:text-ink">Semua semprong</a></li>
                <li><a wire:navigate href="{{ route('keranjang') }}" class="hover:text-ink">Tas belanja</a></li>
            </ul>
        </div>
        <div>
            <h2 class="font-sans text-[15px] font-medium tracking-normal text-ink">Bantuan</h2>
            <ul class="mt-3 flex flex-col gap-2">
                <li><a wire:navigate href="{{ route('history') }}" class="hover:text-ink">Riwayat pesanan</a></li>
                <li><a href="{{ route('login') }}" class="hover:text-ink">Masuk ke akun</a></li>
            </ul>
        </div>
        <div>
            <h2 class="font-sans text-[15px] font-medium tracking-normal text-ink">Pembayaran</h2>
            <p class="mt-3 leading-relaxed">Transfer {{ config('toko.rekening.bank') }} {{ config('toko.rekening.nomor') }}<br>a.n. {{ config('toko.rekening.nama') }}</p>
        </div>
    </div>
    <div class="wrap">
        <p class="border-t border-line py-5 text-[13px]">&copy; 2022–{{ date('Y') }} Semprong</p>
    </div>
</footer>
