<div class="mx-auto max-w-6xl px-4 sm:px-6">
    {{-- hero --}}
    <section class="grid items-center gap-10 py-12 md:grid-cols-2 md:py-20">
        <div>
            <span class="badge bg-brand-soft text-brand">Dipanggang setiap hari</span>
            <h1 class="mt-5 text-4xl leading-tight font-bold sm:text-5xl lg:text-6xl">
                Renyah, manis, dan selalu <span class="text-brand italic">bikin kangen.</span>
            </h1>
            <p class="mt-5 max-w-md text-lg text-ink-muted">
                Kue semprong buatan rumah dengan resep turun-temurun, digulung tipis dan dikemas rapat
                supaya tetap garing sampai ke tanganmu.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a wire:navigate href="{{ route('produk') }}" class="btn btn-primary">
                    Belanja sekarang <x-icon name="arrow-right" />
                </a>
                <a href="#terlaris" class="btn btn-secondary">Lihat terlaris</a>
            </div>
        </div>

        <figure class="relative">
            <div class="absolute -inset-2 -z-10 rotate-2 rounded-[2.5rem] bg-brand-soft sm:-inset-4"></div>
            <img src="{{ asset('images/banner-semprong.jpg') }}" alt="Kue semprong di atas piring"
                class="aspect-[4/3] w-full rounded-[2rem] object-cover shadow-xl shadow-brand/10">
            <figcaption class="mt-3 text-right text-xs text-ink-muted">
                Foto: <a href="https://commons.wikimedia.org/wiki/File:Kuih_Kapit_(53564062798).jpg"
                    class="underline-offset-2 hover:underline" target="_blank" rel="noopener">Choo Yut Shing</a>, CC BY 2.0
            </figcaption>
        </figure>
    </section>

    {{-- produk --}}
    <section id="terlaris" class="scroll-mt-24">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold">Produk terbaik</h2>
                <p class="mt-1 text-ink-muted">Varian favorit pelanggan minggu ini.</p>
            </div>
            <a wire:navigate href="{{ route('produk') }}"
                class="hidden items-center gap-1 text-sm font-semibold text-brand hover:underline sm:inline-flex">
                Lihat semua <x-icon name="arrow-right" />
            </a>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($barangs as $barang)
                <x-product-card :barang="$barang" :href="route('produk.detail', $barang->id)" />
            @endforeach
        </div>

        <a wire:navigate href="{{ route('produk') }}" class="btn btn-secondary mt-8 w-full sm:hidden">Lihat semua produk</a>
    </section>
</div>
