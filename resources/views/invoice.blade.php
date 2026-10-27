{{-- Invoice PDF (dompdf): tata letak tabel dan CSS sederhana, karena dompdf tidak mengenal flex/grid. DejaVu bawaan dompdf menampung huruf non-Latin di alamat. --}}
@php($pembayaran = $pesanan->pembayaran)
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $pesanan->nomor }}</title>
    <style>
        @page { margin: 48px 52px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #2B2124; line-height: 1.5; }
        h1, .serif { font-family: 'DejaVu Serif', serif; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; padding: 0; }
        .muted { color: #7A6A6D; }
        .kanan { text-align: right; }
        .angka { white-space: nowrap; }
        .merek { font-family: 'DejaVu Serif', serif; font-size: 26px; font-style: italic; font-weight: bold; color: #5E1A24; }
        .pita { height: 4px; background: #E8D5B5; border-bottom: 1px solid #A88A57; margin: 18px 0 26px; }
        h1 { font-size: 22px; font-weight: normal; color: #5E1A24; margin: 0; letter-spacing: 1px; }
        .label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #A88A57; margin-bottom: 4px; }
        .barang th { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #A88A57; font-weight: normal; text-align: left; padding: 0 0 8px; border-bottom: 1px solid #5E1A24; }
        .barang td { padding: 10px 0; border-bottom: 1px solid #EDE4DA; }
        .barang th.kanan { text-align: right; }
        .rincian td { padding: 3px 0; }
        .rincian .total td { padding-top: 10px; border-top: 1px solid #5E1A24; font-size: 14px; color: #5E1A24; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 2px; font-size: 9px; letter-spacing: 1px; text-transform: uppercase; }
        .dibayar { background: #E6F0E6; color: #2F6B3A; }
        .belum { background: #F7EDD9; color: #8A5A12; }
        .batal { background: #F3E3E3; color: #8A2A2A; }
        .kaki { margin-top: 40px; padding-top: 12px; border-top: 1px solid #EDE4DA; font-size: 9px; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td>
                <div class="merek">Semprong</div>
                <div class="muted">Semprong &amp; hampers untuk setiap perayaan</div>
            </td>
            <td class="kanan">
                <h1>INVOICE</h1>
                <div class="angka" style="font-size: 12px; margin-top: 4px;">{{ $pesanan->nomor }}</div>
                <div class="muted">@tanggal($pesanan->created_at)</div>
            </td>
        </tr>
    </table>

    <div class="pita"></div>

    <table>
        <tr>
            <td style="width: 50%; padding-right: 24px;">
                <div class="label">Ditagihkan kepada</div>
                <div><strong>{{ $pesanan->user->name }}</strong></div>
                <div class="muted">{{ $pesanan->user->email }}</div>
            </td>
            <td style="width: 50%;">
                <div class="label">Dikirim ke</div>
                <div><strong>{{ $pesanan->alamat['penerima'] }}</strong> <span class="muted">· {{ $pesanan->alamat['no_hp'] }}</span></div>
                <div class="muted">{{ $pesanan->alamat['alamat'] }}, {{ $pesanan->alamat['kode_pos'] }}</div>
                @if ($pesanan->alamat['area_nama'] ?? null)
                    <div class="muted">{{ $pesanan->alamat['area_nama'] }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="barang" style="margin-top: 28px;">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="kanan" style="width: 15%;">Harga</th>
                <th class="kanan" style="width: 10%;">Jumlah</th>
                <th class="kanan" style="width: 18%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pesanan->items as $item)
                <tr>
                    <td>
                        <div>{{ $item->nama_produk }}</div>
                        <div class="muted">{{ $item->nama_varian }} · {{ $item->sku }}</div>
                    </td>
                    <td class="kanan angka">@rupiah($item->harga)</td>
                    <td class="kanan angka">{{ $item->jumlah }}</td>
                    <td class="kanan angka">@rupiah($item->jumlahHarga())</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 52%; padding-right: 24px;">
                <div class="label">Status pembayaran</div>
                @if ($pesanan->dibayar_at)
                    <span class="status dibayar">Dibayar</span>
                    <div class="muted" style="margin-top: 6px;">
                        @tanggal($pesanan->dibayar_at){{ $pembayaran?->metode ? ' · '.$pembayaran->labelMetode() : '' }}
                    </div>
                @elseif ($pembayaran?->status === \App\Enums\StatusPembayaran::DibayarTerlambat)
                    {{-- uang masuk sesudah pesanan berakhir; pesanannya tidak diproses --}}
                    <span class="status batal">Dibayar terlambat</span>
                    <div class="muted" style="margin-top: 6px;">Pesanan {{ str($pesanan->status->label())->lower() }} sebelum pembayaran masuk</div>
                @elseif ($pesanan->menungguPembayaran())
                    <span class="status belum">Belum dibayar</span>
                    <div class="muted" style="margin-top: 6px;">Bayar sebelum @tanggal($pesanan->batas_bayar)</div>
                @else
                    <span class="status batal">Tidak dibayar</span>
                    <div class="muted" style="margin-top: 6px;">{{ $pesanan->status === \App\Enums\StatusPesanan::MenungguPembayaran ? 'Lewat batas bayar' : 'Pesanan '.str($pesanan->status->label())->lower() }}</div>
                @endif

                @if ($pesanan->pengiriman)
                    <div class="label" style="margin-top: 16px;">Pengiriman</div>
                    <div>{{ $pesanan->pengiriman->label() }}{{ $pesanan->pengiriman->resi ? ' · Resi '.$pesanan->pengiriman->resi : '' }}</div>
                @endif
            </td>
            <td style="width: 48%;">
                <table class="rincian">
                    <tr>
                        <td class="muted">Subtotal</td>
                        <td class="kanan angka">@rupiah($pesanan->subtotal)</td>
                    </tr>
                    <tr>
                        <td class="muted">Ongkir{{ $pesanan->pengiriman ? ' '.$pesanan->pengiriman->nama_kurir : '' }}</td>
                        <td class="kanan angka">@rupiah($pesanan->ongkir)</td>
                    </tr>
                    @if ($pesanan->diskon)
                        <tr>
                            <td class="muted">Diskon{{ $pesanan->voucher_kode ? ' '.$pesanan->voucher_kode : '' }}</td>
                            <td class="kanan angka">−@rupiah($pesanan->diskon)</td>
                        </tr>
                    @endif
                    <tr class="total">
                        <td class="serif">Total</td>
                        <td class="kanan angka serif">@rupiah($pesanan->total)</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="kaki muted">
        Terima kasih telah berbelanja di Semprong. Invoice ini dibuat otomatis dan sah tanpa tanda tangan.
    </div>
</body>
</html>
