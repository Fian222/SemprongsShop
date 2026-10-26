{{--
    PROTOTYPE #11 varian A — "Surat Cinta": warisan Peranakan, editorial.
    Palet: giok #0F5B4D, giok tua #0A3F36, porselen #F4F7F6, merah muda nyonya #EFB8C2, emas #B88A2D, tinta #12231F.
    Huruf: Gloock (judul) + Albert Sans (teks).
    Elemen khas: motif ubin Peranakan sebagai pita, dan foto berbingkai lengkung seperti jendela rumah toko.
--}}
<link href="https://fonts.bunny.net/css?family=gloock:400|albert-sans:400,500,600,700&display=swap" rel="stylesheet" />

@php
    $ubin = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect width="64" height="64" fill="#F4F7F6"/><path d="M32 5 59 32 32 59 5 32Z" fill="none" stroke="#B88A2D" stroke-width="1.4"/><g fill="#0F5B4D"><circle cx="0" cy="0" r="11"/><circle cx="64" cy="0" r="11"/><circle cx="0" cy="64" r="11"/><circle cx="64" cy="64" r="11"/><ellipse cx="32" cy="20" rx="5" ry="8"/><ellipse cx="32" cy="44" rx="5" ry="8"/><ellipse cx="20" cy="32" rx="8" ry="5"/><ellipse cx="44" cy="32" rx="8" ry="5"/></g><circle cx="32" cy="32" r="7" fill="#EFB8C2"/><circle cx="32" cy="32" r="2.5" fill="#B88A2D"/><g fill="#EFB8C2"><circle cx="0" cy="0" r="4"/><circle cx="64" cy="0" r="4"/><circle cx="0" cy="64" r="4"/><circle cx="64" cy="64" r="4"/></g></svg>');
@endphp

<style>
    body { background:#f4f7f6 !important; }
    .pa { --jade:#0f5b4d; --jade-deep:#0a3f36; --porcelain:#f4f7f6; --pink:#efb8c2; --gold:#b88a2d; --ink:#12231f; --muted:#4f625d; --line:#d5dfdc;
        font-family:'Albert Sans',system-ui,sans-serif; color:var(--ink); background:var(--porcelain); }
    .pa * { box-sizing:border-box; }
    .pa a { color:inherit; text-decoration:none; }
    .pa :focus-visible { outline:2px solid var(--jade); outline-offset:3px; }
    .pa h1, .pa h2, .pa h3, .pa .serif { font-family:'Gloock',Georgia,serif; font-weight:400; letter-spacing:-.01em; }
    .pa .wrap { max-width:1280px; margin:0 auto; padding:0 16px; }
    @media (min-width:768px) { .pa .wrap { padding:0 40px; } }
    /* padding vertikal ditulis setelah .wrap supaya tidak ditimpa shorthand padding milik .wrap */
    .pa .pa-sec { padding-block:88px; }
    .pa .pa-hero { padding-block:56px 72px; }
    @media (min-width:1000px) { .pa .pa-hero { padding-block:72px 96px; } }
    .pa-tile { background:url("data:image/svg+xml,{{ $ubin }}") 0 0/64px 64px; }

    .pa-ann { background:var(--jade-deep); color:#cfe3dc; font-size:13px; text-align:center; padding:9px 16px; }
    .pa-nav { display:grid; grid-template-columns:1fr auto 1fr; align-items:center; height:80px; border-bottom:1px solid var(--line); }
    .pa-nav .links { display:none; gap:28px; font-size:15px; font-weight:500; }
    @media (min-width:900px) { .pa-nav .links { display:flex; } }
    .pa-nav .links a:hover { color:var(--jade); }
    .pa-nav .mark { font-family:'Gloock',serif; font-size:28px; color:var(--jade); text-align:center; line-height:1; }
    .pa-nav .mark small { display:block; font-family:'Albert Sans',sans-serif; font-size:11px; letter-spacing:.32em; color:var(--gold); margin-top:4px; }
    .pa-nav .icons { display:flex; justify-content:flex-end; gap:6px; }
    .pa-nav .icons a { width:42px; height:42px; display:grid; place-items:center; border-radius:99px; position:relative; }
    .pa-nav .icons a:hover { background:#e6eeeb; }
    .pa-nav .icons b { position:absolute; top:3px; right:2px; min-width:18px; height:18px; border-radius:99px; background:var(--jade); color:#fff; font-size:11px; display:grid; place-items:center; }

    .pa-hero { display:grid; gap:40px; align-items:center; padding:56px 0 72px; }
    @media (min-width:1000px) { .pa-hero { grid-template-columns:1fr 380px 1fr; gap:56px; padding:72px 0 96px; } }
    .pa-hero h1 { font-size:clamp(40px,5vw,68px); line-height:1.02; margin:0; color:var(--jade-deep); }
    .pa-arch { position:relative; width:100%; max-width:380px; margin:0 auto; aspect-ratio:380/520; }
    .pa-arch::before { content:''; position:absolute; inset:-14px; border:1.5px solid var(--gold); border-radius:999px 999px 0 0; }
    .pa-arch img { position:relative; width:100%; height:100%; object-fit:cover; border-radius:999px 999px 0 0; display:block; }
    .pa-hero .side p { font-size:18px; line-height:1.65; color:var(--muted); margin:0 0 24px; max-width:36ch; }
    .pa-hero .from { font-size:15px; color:var(--muted); margin-bottom:20px; }
    .pa-hero .from strong { display:block; font-family:'Gloock',serif; font-weight:400; font-size:30px; color:var(--ink); }
    .pa-btn { display:inline-flex; align-items:center; justify-content:center; height:52px; padding:0 28px; border-radius:99px; font-weight:600; font-size:15px; border:0; cursor:pointer; font-family:inherit; }
    .pa-btn.solid { background:var(--jade); color:#fff; } .pa-btn.solid:hover { background:var(--jade-deep); }
    .pa-btn.line { border:1.5px solid var(--jade); color:var(--jade); background:transparent; } .pa-btn.line:hover { background:#e6eeeb; }
    .pa-btn.pink { background:var(--pink); color:var(--jade-deep); } .pa-btn.pink:hover { background:#f5cbd2; }

    .pa-band { height:64px; border-top:1.5px solid var(--gold); border-bottom:1.5px solid var(--gold); }

    .pa-sec { padding:88px 0; }
    .pa-head { display:flex; flex-wrap:wrap; align-items:end; justify-content:space-between; gap:16px; margin-bottom:48px; }
    .pa-head h2 { font-size:clamp(32px,3.6vw,48px); line-height:1.05; margin:0; color:var(--jade-deep); }
    .pa-head p { margin:0; max-width:42ch; color:var(--muted); line-height:1.6; }
    .pa-windows { display:grid; grid-template-columns:repeat(2,1fr); gap:28px 20px; }
    @media (min-width:900px) { .pa-windows { grid-template-columns:repeat(5,1fr); } }
    .pa-window { text-align:center; background:none; border:0; padding:0; cursor:pointer; font:inherit; color:inherit; }
    .pa-window .img { aspect-ratio:3/4; border-radius:999px 999px 0 0; overflow:hidden; outline:1.5px solid transparent; outline-offset:6px; transition:outline-color .2s; }
    .pa-window img { width:100%; height:100%; object-fit:cover; }
    .pa-window[aria-pressed="true"] .img { outline-color:var(--gold); }
    .pa-window h3 { font-size:22px; margin:18px 0 4px; }
    .pa-window span { font-size:14px; color:var(--muted); }

    .pa-feature { display:grid; gap:40px; margin-top:72px; padding:40px; background:#fff; border:1px solid var(--line); }
    @media (min-width:900px) { .pa-feature { grid-template-columns:420px 1fr; gap:64px; padding:56px; } }
    .pa-feature .pa-arch { max-width:420px; }
    .pa-feature h3 { font-size:clamp(32px,3vw,44px); margin:0; color:var(--jade-deep); }
    .pa-feature .note { color:var(--muted); font-size:17px; line-height:1.6; margin:12px 0 28px; max-width:44ch; }
    .pa-feature fieldset { border:0; padding:0; margin:0 0 24px; }
    .pa-feature legend { font-weight:600; font-size:15px; margin-bottom:12px; }
    .pa-opts { display:flex; flex-wrap:wrap; gap:10px; }
    .pa-opts label { cursor:pointer; }
    .pa-opts input { position:absolute; opacity:0; }
    .pa-opts span { display:block; padding:12px 18px; border:1.5px solid var(--line); border-radius:99px; font-size:15px; }
    .pa-opts input:checked + span { border-color:var(--jade); background:var(--jade); color:#fff; }
    .pa-opts input:focus-visible + span { outline:2px solid var(--jade); outline-offset:3px; }
    .pa-price { display:flex; align-items:baseline; gap:12px; margin-bottom:28px; }
    .pa-price strong { font-family:'Gloock',serif; font-weight:400; font-size:36px; }
    .pa-price s { color:var(--muted); font-size:17px; }
    .pa-added { margin-top:14px; font-size:14px; color:var(--jade); }

    .pa-gift { background:var(--jade-deep); color:#d5e6e0; }
    .pa-gift .pa-head h2 { color:#fff; } .pa-gift .pa-head p { color:#a9c7bd; }
    .pa-gifts { display:grid; gap:32px; }
    @media (min-width:900px) { .pa-gifts { grid-template-columns:repeat(2,1fr); gap:48px; } }
    .pa-gifts article { display:grid; grid-template-columns:160px 1fr; gap:28px; align-items:center; }
    @media (min-width:600px) { .pa-gifts article { grid-template-columns:220px 1fr; } }
    .pa-gifts .img { aspect-ratio:3/4; border-radius:999px 999px 0 0; overflow:hidden; border:6px solid var(--porcelain); }
    .pa-gifts img { width:100%; height:100%; object-fit:cover; }
    .pa-gifts h3 { font-size:28px; color:#fff; margin:0 0 8px; }
    .pa-gifts p { margin:0 0 6px; line-height:1.55; }
    .pa-gifts .price { font-family:'Gloock',serif; font-size:24px; color:var(--pink); margin:12px 0 18px; }

    .pa-story { display:grid; gap:32px; }
    @media (min-width:900px) { .pa-story { grid-template-columns:1fr 1.2fr; gap:80px; } }
    .pa-story h2 { font-size:clamp(32px,3.6vw,48px); line-height:1.05; margin:0; color:var(--jade-deep); }
    .pa-story p { font-size:18px; line-height:1.75; color:var(--muted); margin:0 0 20px; max-width:60ch; }

    .pa-foot { background:var(--jade-deep); color:#a9c7bd; font-size:14px; }
    .pa-foot .cols { display:grid; gap:32px; padding:56px 0; }
    @media (min-width:800px) { .pa-foot .cols { grid-template-columns:2fr 1fr 1fr 1fr; } }
    .pa-foot h3 { font-family:'Albert Sans',sans-serif; font-weight:600; font-size:15px; color:#fff; margin:0 0 12px; letter-spacing:0; }
    .pa-foot ul { list-style:none; margin:0; padding:0; display:grid; gap:8px; }
    .pa-foot a:hover { color:#fff; }
    .pa-foot .mark { font-family:'Gloock',serif; font-size:30px; color:#fff; }
    .pa-foot .legal { border-top:1px solid #1d5a4e; padding:20px 0 90px; }
</style>

<div class="pa" x-data="{ pilih: 0, kemasan: 1, rasa: {{ json_encode(array_column($rasa, 'nama')) }}, catatan: {{ json_encode(array_column($rasa, 'catatan')) }}, foto: {{ json_encode(array_column($rasa, 'foto')) }}, harga: {{ json_encode($kemasan) }}, ditambah: false }">
    <p class="pa-ann">Dikirim ke seluruh Indonesia dengan kurir pilihanmu. Bayar lewat QRIS, e-wallet, atau virtual account.</p>
    <header class="wrap pa-nav">
        <nav class="links" aria-label="Utama">
            <a href="#koleksi">Koleksi</a><a href="#hampers">Hampers</a><a href="#cerita">Cerita kami</a>
        </nav>
        <a href="{{ route('home') }}" class="mark" aria-label="Semprong, beranda">Semprong</a>
        <div class="icons">
            <a href="#" aria-label="Cari"><x-icon name="search" class="size-5" /></a>
            <a href="{{ route('login') }}" aria-label="Akun"><x-icon name="users" class="size-5" /></a>
            <a href="#" aria-label="Tas belanja, 2 barang"><x-icon name="shopping-bag" class="size-5" /><b>2</b></a>
        </div>
    </header>

    <main>
        <section class="wrap pa-hero">
            <h1>Digulung tipis, dikirim sebagai tanda sayang</h1>
            <div class="pa-arch"><img src="{{ asset('images/banner-semprong.jpg') }}" alt="Semprong bermotif di atas piring porselen" style="object-position:62% 50%"></div>
            <div class="side">
                <p>Setiap lembar dicetak di atas besi bermotif, dipanggang di atas bara, lalu digulung selagi hangat. Kami mengemasnya dalam toples dan kaleng yang pantas dihadiahkan.</p>
                <p class="from">Mulai dari <strong>{{ $rp($kemasan[0]['harga']) }}</strong></p>
                <div style="display:flex;flex-wrap:wrap;gap:10px">
                    <a href="#koleksi" class="pa-btn solid">Jelajahi koleksi</a>
                    <a href="#hampers" class="pa-btn line">Lihat hampers</a>
                </div>
            </div>
        </section>

        <div class="pa-band pa-tile" role="presentation"></div>

        <section id="koleksi" class="wrap pa-sec">
            <div class="pa-head">
                <h2>Lima rasa, satu cetakan</h2>
                <p>Pilih rasa untuk melihat kemasannya. Semua dibuat setelah kamu pesan.</p>
            </div>
            <div class="pa-windows">
                @foreach ($rasa as $i => $r)
                    <button type="button" class="pa-window" @click="pilih = {{ $i }}; ditambah = false" :aria-pressed="pilih === {{ $i }}">
                        <div class="img"><img src="{{ $r['foto'] }}" alt="" loading="lazy"></div>
                        <h3>{{ $r['nama'] }}</h3>
                        <span>dari {{ $rp($kemasan[0]['harga']) }}</span>
                    </button>
                @endforeach
            </div>

            <div class="pa-feature">
                <div class="pa-arch"><img :src="foto[pilih]" src="{{ $rasa[0]['foto'] }}" alt=""></div>
                <div>
                    <h3 x-text="'Semprong ' + rasa[pilih]">Semprong {{ $rasa[0]['nama'] }}</h3>
                    <p class="note" x-text="catatan[pilih]">{{ $rasa[0]['catatan'] }}</p>
                    <fieldset>
                        <legend>Kemasan</legend>
                        <div class="pa-opts">
                            @foreach ($kemasan as $k => $v)
                                <label><input type="radio" name="pa-kemasan" value="{{ $k }}" x-model.number="kemasan" @checked($k === 1)><span>{{ $v['nama'] }}</span></label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div class="pa-price">
                        <strong x-text="'Rp ' + harga[kemasan].harga.toLocaleString('id-ID')">{{ $rp($kemasan[1]['harga']) }}</strong>
                        <s x-show="harga[kemasan].coret" x-text="harga[kemasan].coret ? 'Rp ' + harga[kemasan].coret.toLocaleString('id-ID') : ''"></s>
                    </div>
                    <button type="button" class="pa-btn solid" @click="ditambah = true">Tambah ke tas</button>
                    <p class="pa-added" x-cloak x-show="ditambah" role="status" x-text="'Semprong ' + rasa[pilih] + ', ' + harga[kemasan].nama + ' masuk ke tas.'"></p>
                </div>
            </div>
        </section>

        <section id="hampers" class="pa-gift">
            <div class="pa-band pa-tile" role="presentation" style="border-color:var(--gold)"></div>
            <div class="wrap pa-sec">
                <div class="pa-head">
                    <h2>Hampers untuk setiap perayaan</h2>
                    <p>Lebaran, Imlek, atau sekadar ucapan terima kasih. Tulis pesanmu, kami selipkan kartunya.</p>
                </div>
                <div class="pa-gifts">
                    @foreach (array_slice($hampers, 0, 2) as $h)
                        <article>
                            <div class="img"><img src="{{ $h['foto'] }}" alt="" loading="lazy"></div>
                            <div>
                                <h3>{{ $h['nama'] }}</h3>
                                <p>{{ $h['isi'] }}</p>
                                <p class="price">{{ $rp($h['harga']) }}</p>
                                <a href="#" class="pa-btn pink">Pilih hampers ini</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="cerita" class="wrap pa-sec pa-story">
            <h2>Dari dapur keluarga, dengan cetakan yang sama</h2>
            <div>
                <p>Adonan santan, telur, dan tepung beras dituang tipis ke cetakan besi bermotif bunga. Motif itulah yang membekas di setiap lembar semprong kami.</p>
                <p>Begitu matang, lembaran digulung cepat selagi lentur, lalu didinginkan sampai renyah. Kami baru membuatnya setelah pesananmu masuk, supaya yang sampai di tanganmu masih garing.</p>
                <a href="#koleksi" class="pa-btn line">Pilih rasamu</a>
            </div>
        </section>
    </main>

    <footer class="pa-foot">
        <div class="pa-band pa-tile" role="presentation"></div>
        <div class="wrap cols">
            <div><p class="mark" style="margin:0 0 10px">Semprong</p><p style="margin:0;max-width:34ch;line-height:1.6">Kue gulung buatan rumah, dikemas untuk dinikmati dan dihadiahkan.</p></div>
            <div><h3>Belanja</h3><ul><li><a href="#koleksi">Koleksi</a></li><li><a href="#hampers">Hampers</a></li><li><a href="#">Hampers korporat</a></li></ul></div>
            <div><h3>Bantuan</h3><ul><li><a href="#">Lacak pesanan</a></li><li><a href="#">Pengiriman</a></li><li><a href="#">Hubungi kami</a></li></ul></div>
            <div><h3>Pembayaran</h3><p style="margin:0;line-height:1.6">QRIS, GoPay, ShopeePay, virtual account, dan kartu kredit lewat Midtrans.</p></div>
        </div>
        <div class="wrap legal">&copy; 2022–{{ date('Y') }} Semprong</div>
    </footer>
</div>
