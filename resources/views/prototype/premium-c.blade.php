{{--
    PROTOTYPE #11 varian C — "Studio": DTC modern dengan blok warna per rasa.
    Palet: tiap rasa punya warna (original #E2A04A, pandan #5E8C3A, cokelat #5A3A2A, keju #F2C94C, gula aren #8E4B2B);
    netral putih, kertas #F2F1EE, dan tinta cokelat pekat #2B1B14 sebagai pengganti hitam.
    Huruf: Schibsted Grotesk saja. Radius besar (28px) untuk blok, kapsul untuk tombol.
    Elemen khas: kata "semprong" selebar layar dan panel produk yang langsung bisa dibeli dari beranda.
--}}
<link href="https://fonts.bunny.net/css?family=schibsted-grotesk:400,500,600,700,800,900&display=swap" rel="stylesheet" />

<style>
    body { background:#fff !important; }
    .pc { --ink:#2b1b14; --muted:#6e5d55; --paper:#f2f1ee; --line:#e2dfd9;
        font-family:'Schibsted Grotesk',system-ui,sans-serif; color:var(--ink); background:#fff; }
    .pc * { box-sizing:border-box; }
    .pc a { color:inherit; text-decoration:none; }
    .pc :focus-visible { outline:3px solid var(--ink); outline-offset:3px; }
    .pc h1, .pc h2, .pc h3 { font-family:inherit; letter-spacing:-.035em; }
    .pc .wrap { max-width:1400px; margin:0 auto; padding:0 16px; }
    @media (min-width:768px) { .pc .wrap { padding:0 32px; } }

    .pc-nav { display:flex; align-items:center; gap:28px; height:72px; }
    .pc-nav .mark { font-weight:900; font-size:24px; letter-spacing:-.05em; }
    .pc-nav .links { display:none; gap:24px; font-weight:500; }
    @media (min-width:860px) { .pc-nav .links { display:flex; } }
    .pc-nav .links a { color:var(--muted); } .pc-nav .links a:hover { color:var(--ink); }
    .pc-nav .right { margin-left:auto; display:flex; gap:8px; align-items:center; }
    .pc-pill { display:inline-flex; align-items:center; justify-content:center; gap:8px; height:44px; padding:0 20px; border-radius:99px; font:inherit; font-weight:600; font-size:15px; border:0; cursor:pointer; }
    .pc-pill.ink { background:var(--ink); color:#fff; } .pc-pill.ink:hover { background:#4a3127; }
    .pc-pill.soft { background:var(--paper); color:var(--ink); } .pc-pill.soft:hover { background:var(--line); }
    .pc-pill.big { height:56px; padding:0 30px; font-size:17px; }

    .pc-word { font-weight:900; font-size:clamp(64px,18.6vw,300px); line-height:.8; letter-spacing:-.07em; margin:8px 0 0; white-space:nowrap; }
    .pc-hero { display:grid; gap:16px; margin-top:24px; }
    @media (min-width:960px) { .pc-hero { grid-template-columns:2fr 1fr; } }
    .pc-hero .photo { border-radius:28px; overflow:hidden; min-height:300px; position:relative; }
    .pc-hero .photo img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
    .pc-hero .panel { border-radius:28px; background:#e2a04a; padding:32px; display:flex; flex-direction:column; justify-content:space-between; gap:32px; min-height:300px; }
    .pc-hero .panel p { font-size:clamp(24px,2.2vw,32px); font-weight:700; line-height:1.12; letter-spacing:-.025em; margin:0; }
    .pc-hero .panel small { display:block; font-size:16px; font-weight:500; margin-bottom:16px; }

    .pc-sec { padding:80px 0; }
    .pc-head { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:end; gap:16px; margin-bottom:28px; }
    .pc-head h2 { font-size:clamp(36px,4.4vw,64px); line-height:.95; font-weight:800; margin:0; }
    .pc-head p { margin:0; color:var(--muted); max-width:36ch; font-size:17px; line-height:1.5; }

    .pc-rail { display:grid; grid-auto-flow:column; grid-auto-columns:min(80vw,320px); gap:16px; overflow-x:auto; scroll-snap-type:x mandatory; padding-bottom:8px; scrollbar-width:thin; }
    .pc-block { scroll-snap-align:start; border-radius:28px; padding:24px; min-height:420px; display:flex; flex-direction:column; text-align:left; border:0; font:inherit; cursor:pointer; position:relative; }
    .pc-block .disc { width:150px; height:150px; border-radius:50%; overflow:hidden; box-shadow:0 0 0 6px rgba(255,255,255,.35); }
    .pc-block .disc img { width:100%; height:100%; object-fit:cover; }
    .pc-block h3 { font-size:44px; font-weight:800; line-height:.95; margin:auto 0 10px; }
    .pc-block p { margin:0 0 14px; font-size:15px; line-height:1.45; opacity:.85; }
    .pc-block .from { font-weight:700; }
    .pc-block[aria-pressed="true"]::after { content:''; position:absolute; inset:8px; border-radius:22px; border:2px solid currentColor; opacity:.6; pointer-events:none; }

    .pc-focus { display:grid; gap:16px; }
    @media (min-width:960px) { .pc-focus { grid-template-columns:1fr 1fr; } }
    .pc-focus .stage { border-radius:28px; display:grid; place-items:center; min-height:420px; padding:40px; transition:background-color .3s; }
    .pc-focus .stage .disc { width:min(78%,420px); aspect-ratio:1; border-radius:50%; overflow:hidden; box-shadow:0 30px 60px -20px rgba(0,0,0,.45); }
    .pc-focus .stage img { width:100%; height:100%; object-fit:cover; }
    .pc-focus .buy { border-radius:28px; background:var(--paper); padding:32px; display:flex; flex-direction:column; }
    @media (min-width:768px) { .pc-focus .buy { padding:48px; } }
    .pc-focus h3 { font-size:clamp(40px,4vw,60px); font-weight:800; line-height:.95; margin:0 0 14px; }
    .pc-focus .note { font-size:18px; color:var(--muted); line-height:1.5; margin:0 0 32px; max-width:40ch; }
    .pc-seg { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; background:#fff; border-radius:99px; padding:6px; margin-bottom:24px; }
    .pc-seg button { border:0; background:none; font:inherit; font-weight:600; font-size:14px; border-radius:99px; padding:12px 6px; cursor:pointer; color:var(--muted); }
    .pc-seg button[aria-pressed="true"] { background:var(--ink); color:#fff; }
    .pc-row { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-top:auto; padding-top:24px; border-top:1px solid var(--line); }
    .pc-price { font-size:36px; font-weight:800; letter-spacing:-.03em; font-variant-numeric:tabular-nums; }
    .pc-price s { font-size:17px; font-weight:500; color:var(--muted); margin-left:8px; letter-spacing:0; }
    .pc-qty { display:inline-flex; align-items:center; background:#fff; border-radius:99px; }
    .pc-qty button { width:44px; height:44px; border:0; background:none; font-size:20px; cursor:pointer; border-radius:99px; color:var(--ink); }
    .pc-qty button:hover { background:var(--line); }
    .pc-qty output { min-width:28px; text-align:center; font-weight:700; }
    .pc-added { margin:14px 0 0; font-weight:600; }

    .pc-says { display:grid; gap:16px; }
    @media (min-width:900px) { .pc-says { grid-template-columns:repeat(3,1fr); } }
    .pc-says div { border-radius:28px; background:var(--paper); padding:32px; min-height:240px; display:flex; flex-direction:column; justify-content:space-between; }
    .pc-says strong { font-size:28px; line-height:1.05; letter-spacing:-.03em; font-weight:800; }
    .pc-says span { color:var(--muted); line-height:1.5; }

    .pc-foot { background:var(--ink); color:#cbbcb3; margin-top:40px; }
    .pc-foot .wrap { padding-top:56px; padding-bottom:96px; display:grid; gap:32px; }
    @media (min-width:800px) { .pc-foot .wrap { grid-template-columns:2fr 1fr 1fr; } }
    .pc-foot .mark { font-weight:900; font-size:56px; letter-spacing:-.06em; color:#fff; line-height:.9; margin:0; }
    .pc-foot h3 { color:#fff; font-size:15px; letter-spacing:0; margin:0 0 12px; }
    .pc-foot ul { list-style:none; margin:0; padding:0; display:grid; gap:8px; }
    .pc-foot a:hover { color:#fff; }
</style>

<div class="pc" x-data="{
        pilih: 0, kemasan: 1, qty: 1, ditambah: false,
        rasa: {{ json_encode($rasa) }},
        harga: {{ json_encode($kemasan) }},
        rp(n) { return 'Rp ' + n.toLocaleString('id-ID'); },
    }">
    <header class="wrap pc-nav">
        <a href="{{ route('home') }}" class="mark">semprong</a>
        <nav class="links" aria-label="Utama"><a href="#rasa">Rasa</a><a href="#beli">Belanja</a><a href="#">Hampers</a><a href="#">Lacak pesanan</a></nav>
        <div class="right">
            <a href="{{ route('login') }}" class="pc-pill soft">Masuk</a>
            <a href="#" class="pc-pill ink"><x-icon name="shopping-bag" class="size-4" /> Tas (2)</a>
        </div>
    </header>

    <main class="wrap">
        <h1 class="pc-word" aria-label="Semprong">semprong</h1>
        <section class="pc-hero">
            <div class="photo"><img src="{{ asset('images/banner-semprong.jpg') }}" alt="Semprong bermotif di atas piring"></div>
            <div class="panel">
                <p><small>Kue gulung buatan rumah</small>Tipis, renyah, dan baru dicetak setelah kamu pesan.</p>
                <a href="#beli" class="pc-pill ink big" style="align-self:flex-start">Pilih rasamu</a>
            </div>
        </section>

        <section id="rasa" class="pc-sec">
            <div class="pc-head">
                <h2>Lima rasa.<br>Satu cetakan.</h2>
                <p>Geser dan pilih rasa. Panel di bawah langsung berubah, lengkap dengan kemasannya.</p>
            </div>
            <div class="pc-rail">
                @foreach ($rasa as $i => $r)
                    <button type="button" class="pc-block" style="background:{{ $r['warna'] }};color:{{ $r['teks'] }}"
                        :aria-pressed="pilih === {{ $i }}" @click="pilih = {{ $i }}; ditambah = false; document.getElementById('beli').scrollIntoView({ behavior: 'smooth', block: 'start' })">
                        <span class="disc"><img src="{{ $r['foto'] }}" alt="" loading="lazy"></span>
                        <h3>{{ $r['nama'] }}</h3>
                        <p>{{ $r['catatan'] }}</p>
                        <span class="from">dari {{ $rp($kemasan[0]['harga']) }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        <section id="beli" class="pc-focus" style="scroll-margin-top:24px">
            <div class="stage" :style="'background:' + rasa[pilih].warna" style="background:{{ $rasa[0]['warna'] }}">
                <div class="disc"><img :src="rasa[pilih].foto" src="{{ $rasa[0]['foto'] }}" alt=""></div>
            </div>
            <div class="buy">
                <h3 x-text="rasa[pilih].nama">{{ $rasa[0]['nama'] }}</h3>
                <p class="note" x-text="rasa[pilih].catatan">{{ $rasa[0]['catatan'] }}</p>
                <div class="pc-seg" role="group" aria-label="Kemasan">
                    @foreach ($kemasan as $k => $v)
                        <button type="button" :aria-pressed="kemasan === {{ $k }}" @click="kemasan = {{ $k }}">{{ $v['nama'] }}</button>
                    @endforeach
                </div>
                <div class="pc-row">
                    <div class="pc-price">
                        <span x-text="rp(harga[kemasan].harga * qty)">{{ $rp($kemasan[1]['harga']) }}</span><s x-show="harga[kemasan].coret" x-text="harga[kemasan].coret ? rp(harga[kemasan].coret * qty) : ''"></s>
                    </div>
                    <div class="pc-qty">
                        <button type="button" aria-label="Kurangi" @click="qty = Math.max(1, qty - 1)">&minus;</button>
                        <output x-text="qty" aria-live="polite">1</output>
                        <button type="button" aria-label="Tambah" @click="qty++">+</button>
                    </div>
                </div>
                <button type="button" class="pc-pill ink big" style="margin-top:20px;width:100%" @click="ditambah = true">Tambah ke tas</button>
                <p class="pc-added" x-cloak x-show="ditambah" role="status" x-text="qty + ' × ' + rasa[pilih].nama + ', ' + harga[kemasan].nama + ' masuk ke tas.'"></p>
            </div>
        </section>

        <section class="pc-sec pc-says">
            <div><strong>Dicetak setelah kamu pesan.</strong><span>Tidak ada stok lama di gudang. Adonan baru dituang begitu pesananmu masuk.</span></div>
            <div><strong>Bayar dengan cara favoritmu.</strong><span>QRIS, GoPay, ShopeePay, virtual account, atau kartu lewat Midtrans.</span></div>
            <div><strong>Pilih kurirmu sendiri.</strong><span>Ongkir dan estimasi tiba terlihat sebelum bayar, resi langsung bisa dilacak.</span></div>
        </section>
    </main>

    <footer class="pc-foot">
        <div class="wrap">
            <p class="mark">semprong</p>
            <div><h3>Belanja</h3><ul><li><a href="#rasa">Rasa</a></li><li><a href="#">Hampers</a></li><li><a href="#">Korporat</a></li></ul></div>
            <div><h3>Bantuan</h3><ul><li><a href="#">Lacak pesanan</a></li><li><a href="#">Pengiriman</a></li><li><a href="#">Hubungi kami</a></li></ul></div>
        </div>
    </footer>
</div>
