{{--
    PROTOTYPE #11 varian B — "Hampers": toko yang dipimpin hadiah. Hero = pilihan momen, lalu set hadiah,
    susun hampers sendiri, dan jalur korporat. Rasa tunggal jadi pelengkap.
    Palet: marun #5E1A24, marun tua #3F0F17, champagne #E8D5B5, emas #A88A57, kertas #FFFFFF, kabut #F3F1F1, tinta #231A1C.
    Huruf: Bodoni Moda (judul) + Jost (teks).
    Elemen khas: garis pita champagne yang melintasi hero seperti pita kotak hadiah.
--}}
<link href="https://fonts.bunny.net/css?family=bodoni-moda:400,500,600,700|jost:400,500,600&display=swap" rel="stylesheet" />

@php
    $momen = [
        ['kunci' => 'lebaran', 'label' => 'Lebaran', 'judul' => 'Hampers Lebaran', 'teks' => 'Toples kaca dan kotak kayu untuk keluarga dan kerabat. Kirim langsung ke banyak alamat.', 'foto' => $foto(1)],
        ['kunci' => 'imlek', 'label' => 'Imlek', 'judul' => 'Hampers Imlek', 'teks' => 'Kaleng bermotif dan semprong gula aren, manis untuk tahun yang baru.', 'foto' => $foto(5)],
        ['kunci' => 'natal', 'label' => 'Natal', 'judul' => 'Hampers Natal', 'teks' => 'Semprong cokelat dan keju dalam kotak berpita, siap ditaruh di bawah pohon.', 'foto' => $foto(3)],
        ['kunci' => 'terima-kasih', 'label' => 'Terima kasih', 'judul' => 'Untuk ucapan terima kasih', 'teks' => 'Kotak kecil dengan kartu tulisan tangan, untuk guru, rekan, atau tetangga.', 'foto' => $foto(6)],
    ];
@endphp

<style>
    body { background:#fff !important; }
    .pb { --wine:#5e1a24; --wine-deep:#3f0f17; --champ:#e8d5b5; --gold:#a88a57; --paper:#fff; --mist:#f3f1f1; --ink:#231a1c; --muted:#6b5c5f; --line:#e6e0e0;
        font-family:'Jost',system-ui,sans-serif; color:var(--ink); background:var(--paper); }
    .pb * { box-sizing:border-box; }
    .pb a { color:inherit; text-decoration:none; }
    .pb :focus-visible { outline:2px solid var(--gold); outline-offset:3px; }
    .pb h1, .pb h2, .pb h3, .pb .bodoni { font-family:'Bodoni Moda',Didot,serif; font-weight:500; letter-spacing:-.01em; }
    .pb .wrap { max-width:1320px; margin:0 auto; padding:0 16px; }
    @media (min-width:768px) { .pb .wrap { padding:0 40px; } }

    .pb .pb-sec { padding-block:96px; }
    .pb-nav { display:flex; align-items:center; gap:32px; height:76px; border-bottom:1px solid var(--line); }
    .pb-nav .mark { font-family:'Bodoni Moda',serif; font-size:26px; font-weight:600; font-style:italic; color:var(--wine); }
    .pb-nav .links { display:none; gap:28px; font-size:15px; }
    @media (min-width:900px) { .pb-nav .links { display:flex; } }
    .pb-nav .links a:hover { color:var(--wine); }
    .pb-nav .right { margin-left:auto; display:flex; align-items:center; gap:6px; font-size:15px; }
    .pb-nav .right a { padding:10px 12px; }
    .pb-nav .bag { white-space:nowrap; display:flex; align-items:center; gap:8px; border:1px solid var(--ink); border-radius:2px; }
    .pb-nav .bag:hover { background:var(--ink); color:#fff; }

    .pb-hero { display:grid; position:relative; overflow:hidden; }
    @media (min-width:960px) { .pb-hero { grid-template-columns:1fr 1fr; min-height:620px; } }
    .pb-hero .panel { border-radius:0; background:var(--wine); color:#f1e6e1; padding:48px 16px 56px; display:flex; flex-direction:column; justify-content:center; position:relative; z-index:1; }
    @media (min-width:768px) { .pb-hero .panel { padding:64px 64px 72px max(40px, calc((100vw - 1320px) / 2 + 40px)); } }
    .pb-tabs { display:flex; flex-wrap:wrap; gap:4px; margin-bottom:40px; }
    .pb-tabs button { font:inherit; font-size:15px; color:#e6d3cf; background:none; border:0; padding:8px 14px; cursor:pointer; border-bottom:1.5px solid transparent; }
    .pb-tabs button[aria-selected="true"] { color:#fff; border-bottom-color:var(--champ); }
    .pb-hero h1 { font-size:clamp(40px,5vw,72px); line-height:1; margin:0; color:#fff; max-width:12ch; }
    .pb-hero .panel p { font-size:18px; line-height:1.6; max-width:40ch; margin:24px 0 32px; color:#e6d3cf; }
    .pb-hero .photo { position:relative; min-height:360px; }
    .pb-hero .photo img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
    /* pita: garis vertikal + horizontal champagne menyilang foto, seperti pita kotak hadiah */
    .pb-hero .photo::before, .pb-hero .photo::after { content:''; position:absolute; background:var(--champ); z-index:1; box-shadow:0 0 0 1px rgba(63,15,23,.15); }
    .pb-hero .photo::before { top:0; bottom:0; left:38%; width:14px; }
    .pb-hero .photo::after { left:0; right:0; top:58%; height:14px; }
    .pb-bow { position:absolute; z-index:2; left:38%; top:58%; transform:translate(calc(-50% + 7px), calc(-50% + 7px)); width:64px; height:64px; border-radius:50%; background:var(--champ); display:grid; place-items:center; color:var(--wine-deep); font-family:'Bodoni Moda',serif; font-style:italic; font-size:20px; box-shadow:0 6px 18px rgba(63,15,23,.25); }

    .pb-btn { display:inline-flex; align-items:center; justify-content:center; height:52px; padding:0 30px; font:inherit; font-weight:500; font-size:15px; border-radius:2px; border:0; cursor:pointer; }
    .pb-btn.champ { background:var(--champ); color:var(--wine-deep); } .pb-btn.champ:hover { background:#f1e3cb; }
    .pb-btn.ink { background:var(--ink); color:#fff; } .pb-btn.ink:hover { background:var(--wine); }
    .pb-btn.line { border:1px solid var(--ink); color:var(--ink); background:transparent; } .pb-btn.line:hover { background:var(--ink); color:#fff; }

    .pb-sec { padding:96px 0; }
    .pb-head { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:end; gap:16px; margin-bottom:40px; }
    .pb-head h2 { font-size:clamp(32px,3.6vw,52px); line-height:1.05; margin:0; }
    .pb-head p { margin:0; color:var(--muted); max-width:40ch; line-height:1.6; }

    .pb-bento { display:grid; gap:16px; }
    @media (min-width:900px) { .pb-bento { grid-template-columns:1.4fr 1fr 1fr; grid-template-rows:repeat(2, 300px); } .pb-bento article:first-child { grid-row:span 2; } .pb-bento article:last-child { grid-column:span 2; } }
    .pb-bento article { position:relative; overflow:hidden; background:var(--mist); min-height:300px; }
    .pb-bento img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transition:transform .6s; }
    .pb-bento article:hover img { transform:scale(1.03); }
    .pb-bento .info { position:absolute; left:16px; right:16px; bottom:16px; background:#fff; padding:18px 20px; display:flex; justify-content:space-between; align-items:end; gap:12px; }
    .pb-bento h3 { font-size:24px; margin:0 0 4px; line-height:1.1; }
    .pb-bento .info p { margin:0; font-size:14px; color:var(--muted); }
    .pb-bento .info strong { font-weight:500; white-space:nowrap; font-size:17px; }

    .pb-build { background:var(--mist); }
    .pb-build .grid { display:grid; gap:48px; align-items:center; }
    @media (min-width:900px) { .pb-build .grid { grid-template-columns:1fr 1fr; gap:80px; } }
    .pb-steps { list-style:none; margin:32px 0 36px; padding:0; display:grid; gap:0; counter-reset:s; border-top:1px solid #d9d1d1; }
    .pb-steps li { counter-increment:s; display:grid; grid-template-columns:56px 1fr; gap:8px; padding:20px 0; border-bottom:1px solid #d9d1d1; }
    .pb-steps li::before { content:counter(s); font-family:'Bodoni Moda',serif; font-size:32px; line-height:1; color:var(--wine); }
    .pb-steps strong { display:block; font-weight:500; font-size:18px; }
    .pb-steps span { color:var(--muted); font-size:15px; }
    .pb-box { aspect-ratio:1; background:#fff; padding:28px; display:grid; grid-template-columns:repeat(3,1fr); gap:12px; align-content:center; border:1px solid #d9d1d1; position:relative; }
    .pb-box .slot { aspect-ratio:1; border-radius:50%; overflow:hidden; border:1px dashed var(--gold); background:var(--mist); display:grid; place-items:center; color:var(--muted); font-size:13px; text-align:center; }
    .pb-box .slot img { width:100%; height:100%; object-fit:cover; }
    .pb-box .kartu { grid-column:1 / -1; margin-top:12px; padding:16px; border:1px solid var(--line); font-family:'Bodoni Moda',serif; font-style:italic; font-size:19px; color:var(--wine); text-align:center; }

    .pb-corp { background:var(--wine-deep); color:#e6d3cf; }
    .pb-corp .grid { display:grid; gap:32px; align-items:center; }
    @media (min-width:900px) { .pb-corp .grid { grid-template-columns:1.3fr 1fr; } }
    .pb-corp h2 { color:#fff; font-size:clamp(32px,3.4vw,48px); line-height:1.05; margin:0 0 16px; }
    .pb-corp p { margin:0; line-height:1.65; max-width:52ch; }
    .pb-corp ul { list-style:none; padding:0; margin:0 0 28px; display:grid; gap:12px; }
    .pb-corp li { display:flex; gap:10px; }
    .pb-corp li svg { color:var(--champ); flex:none; margin-top:3px; }

    .pb-flavours { display:grid; gap:0; border-top:1px solid var(--line); }
    @media (min-width:800px) { .pb-flavours { grid-template-columns:repeat(5,1fr); border-top:0; } }
    .pb-flavours a { display:flex; align-items:center; gap:14px; padding:16px 0; border-bottom:1px solid var(--line); }
    @media (min-width:800px) { .pb-flavours a { flex-direction:column; align-items:flex-start; padding:0 16px 0 0; border:0; } }
    .pb-flavours img { width:64px; height:64px; border-radius:50%; object-fit:cover; }
    @media (min-width:800px) { .pb-flavours img { width:100%; height:auto; aspect-ratio:1; border-radius:0; } }
    .pb-flavours strong { font-family:'Bodoni Moda',serif; font-weight:500; font-size:20px; display:block; }
    .pb-flavours span { color:var(--muted); font-size:14px; }

    .pb-foot { border-top:1px solid var(--line); font-size:14px; color:var(--muted); }
    .pb-foot .cols { display:grid; gap:28px; padding:56px 0 32px; }
    @media (min-width:800px) { .pb-foot .cols { grid-template-columns:2fr 1fr 1fr 1fr; } }
    .pb-foot h3 { font-family:'Jost',sans-serif; font-weight:500; font-size:15px; color:var(--ink); margin:0 0 12px; letter-spacing:0; }
    .pb-foot ul { list-style:none; margin:0; padding:0; display:grid; gap:8px; }
    .pb-foot a:hover { color:var(--wine); }
    .pb-foot .legal { padding:20px 0 90px; border-top:1px solid var(--line); }
</style>

<div class="pb" x-data="{ momen: 0 }">
    <header class="wrap pb-nav">
        <a href="{{ route('home') }}" class="mark">Semprong</a>
        <nav class="links" aria-label="Utama">
            <a href="#hampers">Hampers</a><a href="#susun">Susun sendiri</a><a href="#rasa">Semprong</a><a href="#korporat">Korporat</a>
        </nav>
        <div class="right">
            <a href="#" aria-label="Cari"><x-icon name="search" class="size-5" /></a>
            <a href="{{ route('login') }}">Masuk</a>
            <a href="#" class="bag"><x-icon name="shopping-bag" class="size-4" /> Tas (2)</a>
        </div>
    </header>

    <main>
        <section class="pb-hero">
            <div class="panel">
                <div class="pb-tabs" role="tablist" aria-label="Momen">
                    @foreach ($momen as $i => $m)
                        <button type="button" role="tab" :aria-selected="momen === {{ $i }}" @click="momen = {{ $i }}">{{ $m['label'] }}</button>
                    @endforeach
                </div>
                @foreach ($momen as $i => $m)
                    <div x-show="momen === {{ $i }}" @if ($i) x-cloak @endif role="tabpanel">
                        <h1>{{ $m['judul'] }}</h1>
                        <p>{{ $m['teks'] }}</p>
                        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center">
                            <a href="#hampers" class="pb-btn champ">Lihat hampers {{ strtolower($m['label']) }}</a>
                            <span style="color:#e6d3cf">mulai {{ $rp(135000) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="photo">
                @foreach ($momen as $i => $m)
                    <img x-show="momen === {{ $i }}" @if ($i) x-cloak @endif src="{{ $i === 0 ? asset('images/banner-semprong.jpg') : $m['foto'] }}" alt="">
                @endforeach
                <span class="pb-bow" aria-hidden="true">S</span>
            </div>
        </section>

        <section id="hampers" class="wrap pb-sec">
            <div class="pb-head">
                <h2>Set hadiah pilihan</h2>
                <p>Setiap set dikemas dengan pita dan kartu ucapan. Tulis pesannya saat checkout.</p>
            </div>
            <div class="pb-bento">
                @foreach ($hampers as $h)
                    <article>
                        <img src="{{ $h['foto'] }}" alt="" loading="lazy">
                        <div class="info">
                            <div><h3>{{ $h['nama'] }}</h3><p>{{ $h['isi'] }}</p></div>
                            <strong>{{ $loop->last ? 'mulai ' : '' }}{{ $rp($h['harga']) }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="susun" class="pb-build pb-sec">
            <div class="wrap grid">
                <div>
                    <h2 style="font-size:clamp(32px,3.6vw,52px);line-height:1.05;margin:0">Susun hampersmu sendiri</h2>
                    <ol class="pb-steps">
                        <li><div><strong>Pilih kotak</strong><span>Kotak kertas, kotak kayu, atau kaleng bermotif.</span></div></li>
                        <li><div><strong>Pilih tiga rasa</strong><span>Dari lima rasa semprong kami.</span></div></li>
                        <li><div><strong>Tulis kartu ucapan</strong><span>Kami cetak dan selipkan di dalam kotak.</span></div></li>
                    </ol>
                    <a href="#" class="pb-btn ink">Mulai menyusun</a>
                </div>
                <div class="pb-box" aria-hidden="true">
                    <div class="slot"><img src="{{ $rasa[1]['foto'] }}" alt=""></div>
                    <div class="slot"><img src="{{ $rasa[2]['foto'] }}" alt=""></div>
                    <div class="slot">Rasa ketiga</div>
                    <div class="kartu">Selamat Idulfitri, mohon maaf lahir dan batin.</div>
                </div>
            </div>
        </section>

        <section id="rasa" class="wrap pb-sec">
            <div class="pb-head">
                <h2>Atau cukup semprongnya</h2>
                <p>Toples 250 g, 500 g, atau kaleng 750 g untuk dinikmati sendiri.</p>
            </div>
            <div class="pb-flavours">
                @foreach ($rasa as $r)
                    <a href="#"><img src="{{ $r['foto'] }}" alt="" loading="lazy"><div><strong>{{ $r['nama'] }}</strong><span>mulai {{ $rp($kemasan[0]['harga']) }}</span></div></a>
                @endforeach
            </div>
        </section>

        <section id="korporat" class="pb-corp pb-sec">
            <div class="wrap grid">
                <div>
                    <h2>Hampers korporat untuk klien dan karyawan</h2>
                    <p>Pesan mulai 20 kotak. Logo perusahaan di kartu, kirim ke banyak alamat sekaligus, dan faktur untuk keperluan pajak.</p>
                </div>
                <div>
                    <ul>
                        <li><x-icon name="check" class="size-4" /> Harga bertingkat sesuai jumlah</li>
                        <li><x-icon name="check" class="size-4" /> Satu tagihan untuk semua alamat</li>
                        <li><x-icon name="check" class="size-4" /> Jadwal kirim sesuai tanggal acara</li>
                    </ul>
                    <a href="#" class="pb-btn champ">Minta penawaran</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="pb-foot">
        <div class="wrap cols">
            <div><p class="bodoni" style="font-size:26px;font-style:italic;color:var(--wine);margin:0 0 10px">Semprong</p><p style="margin:0;max-width:34ch;line-height:1.6">Hampers dan kue gulung buatan rumah untuk setiap perayaan.</p></div>
            <div><h3>Belanja</h3><ul><li><a href="#hampers">Hampers</a></li><li><a href="#susun">Susun sendiri</a></li><li><a href="#rasa">Semprong</a></li></ul></div>
            <div><h3>Bantuan</h3><ul><li><a href="#">Lacak pesanan</a></li><li><a href="#">Pengiriman</a></li><li><a href="#korporat">Korporat</a></li></ul></div>
            <div><h3>Pembayaran</h3><p style="margin:0;line-height:1.6">QRIS, e-wallet, virtual account, dan kartu lewat Midtrans.</p></div>
        </div>
        <div class="wrap legal">&copy; 2022–{{ date('Y') }} Semprong</div>
    </footer>
</div>
