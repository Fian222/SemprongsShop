<div>
    <section class="grid lg:min-h-[600px] lg:grid-cols-2">
        <div class="flex flex-col justify-center bg-wine px-4 py-12 text-on-wine md:px-10 md:py-16 lg:pl-[max(2.5rem,calc((100vw-1320px)/2+2.5rem))]">
            <h1 class="max-w-[12ch] text-[clamp(2.5rem,5vw,4.5rem)] leading-none text-on-wine-strong">Semprong untuk dinikmati dan dihadiahkan</h1>
            <p class="mt-6 max-w-[40ch] text-lg leading-relaxed">
                Setiap lembar dicetak di atas besi bermotif, dipanggang di atas bara, lalu digulung selagi hangat.
            </p>
            <div class="mt-8 flex flex-wrap items-center gap-4">
                <a wire:navigate href="{{ route('produk') }}" class="btn btn-champ">Pilih semprong</a>
                @if ($hargaMulai)
                    <span>mulai @rupiah($hargaMulai)</span>
                @endif
            </div>
        </div>
        <div class="relative min-h-[340px] overflow-hidden bg-surface-muted">
            <img src="{{ asset('images/banner-semprong.jpg') }}" alt="Semprong bermotif di atas piring"
                class="absolute inset-0 size-full object-cover">
            <x-pita />
            <p class="absolute right-3 bottom-3 bg-wine-deep/75 px-2 py-1 text-[11px] text-on-wine-strong">
                Foto: <a href="https://commons.wikimedia.org/wiki/File:Kuih_Kapit_(53564062798).jpg"
                    class="underline-offset-2 hover:underline" target="_blank" rel="noopener">Choo Yut Shing</a>, CC BY 2.0
            </p>
        </div>
    </section>

    <section id="produk" class="wrap scroll-mt-24 py-20 md:py-24">
        <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
            <h2 class="text-[clamp(2rem,3.6vw,3.25rem)] leading-[1.05]">Pilihan semprong</h2>
            <p class="max-w-[40ch] leading-relaxed text-ink-muted">
                {{ $jumlahProduk }} pilihan, semuanya baru dibuat setelah kamu pesan.
                @if ($jumlahProduk > $produks->count())
                    <a wire:navigate href="{{ route('produk') }}" class="link">Lihat semua</a>
                @endif
            </p>
        </div>

        @if ($produks->isEmpty())
            <div class="card"><x-empty icon="package">Belum ada produk. Rasa baru akan tampil di sini begitu ditambahkan.</x-empty></div>
        @else
            <div class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 md:gap-x-6 xl:grid-cols-4">
                @foreach ($produks as $produk)
                    <x-product-card :produk="$produk" wire:key="produk-{{ $produk->id }}" />
                @endforeach
            </div>
        @endif
    </section>

    <section class="bg-surface-muted py-20 md:py-24">
        <div class="wrap grid gap-12 lg:grid-cols-2 lg:gap-20">
            <div>
                <h2 class="text-[clamp(2rem,3.6vw,3.25rem)] leading-[1.05]">Cara memesan</h2>
                <p class="mt-4 max-w-[44ch] leading-relaxed text-ink-muted">
                    Pesanan dibayar lewat transfer bank, lalu admin mengecek pembayaranmu dan menyiapkan paket.
                </p>
            </div>
            <ol class="border-t border-line-strong">
                @foreach ([
                    ['Pilih rasa dan jumlah', 'Stok di setiap produk selalu terkini.'],
                    ['Isi alamat dan No. HP', 'Di halaman checkout.'],
                    ['Transfer sesuai total', 'Ke '.config('toko.rekening.bank').' '.config('toko.rekening.nomor').' a.n. '.config('toko.rekening.nama').'.'],
                    ['Pesanan dikonfirmasi', 'Statusnya berubah jadi Sudah Lunas di Riwayat pesanan.'],
                ] as [$judul, $ket])
                    <li class="grid grid-cols-[3.5rem_1fr] border-b border-line-strong py-5">
                        <span class="font-display text-[32px] leading-none text-brand">{{ $loop->iteration }}</span>
                        <div>
                            <p class="text-lg font-medium">{{ $judul }}</p>
                            <p class="text-[15px] text-ink-muted">{{ $ket }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
</div>
