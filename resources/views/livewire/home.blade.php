{{--
    PROTOTYPE #11 (branch prototype/premium, jangan di-merge ke main):
    tiga arah visual brand premium, dipilih lewat ?variant=A|B|C. Tanpa parameter = tampilan sekarang.
    Katalog (varian kemasan, hampers) masih data statis: domain barunya baru dibangun di #13.
    Tombol tambah ke tas hanya stub di memori.
--}}
@php
    $variant = in_array(request('variant'), ['A', 'B', 'C']) ? request('variant') : null;
    $foto = fn ($n) => url('build/images/proto-'.$n.'.jpg');

    // harga contoh, bukan harga sungguhan
    $kemasan = [
        ['nama' => 'Toples 250 g', 'harga' => 45000, 'coret' => null],
        ['nama' => 'Toples 500 g', 'harga' => 85000, 'coret' => null],
        ['nama' => 'Kaleng 750 g', 'harga' => 135000, 'coret' => 150000],
    ];
    $rasa = [
        ['nama' => 'Original Wijen', 'catatan' => 'Gurih santan, taburan wijen sangrai.', 'foto' => $foto(1), 'warna' => '#E2A04A', 'teks' => '#2B1B14'],
        ['nama' => 'Pandan', 'catatan' => 'Daun pandan segar, wangi dan lembut.', 'foto' => $foto(2), 'warna' => '#5E8C3A', 'teks' => '#FFFFFF'],
        ['nama' => 'Cokelat', 'catatan' => 'Kakao Jawa, pahit-manis yang dalam.', 'foto' => $foto(3), 'warna' => '#5A3A2A', 'teks' => '#FFFFFF'],
        ['nama' => 'Keju', 'catatan' => 'Keju edam parut, asin yang pas.', 'foto' => $foto(4), 'warna' => '#F2C94C', 'teks' => '#2B1B14'],
        ['nama' => 'Gula Aren', 'catatan' => 'Gula aren cair, karamel yang hangat.', 'foto' => $foto(5), 'warna' => '#8E4B2B', 'teks' => '#FFFFFF'],
    ];
    $hampers = [
        ['nama' => 'Kotak Surat Cinta', 'isi' => '2 toples 250 g dan kartu ucapan', 'harga' => 165000, 'foto' => $foto(6)],
        ['nama' => 'Hampers Rasa Rumah', 'isi' => '3 toples 500 g dalam kotak kayu', 'harga' => 245000, 'foto' => $foto(1)],
        ['nama' => 'Kaleng Pusaka', 'isi' => '1 kaleng 750 g bermotif ubin', 'harga' => 135000, 'foto' => $foto(3)],
        ['nama' => 'Hampers Korporat', 'isi' => 'Mulai 20 kotak, logo di kartu', 'harga' => 350000, 'foto' => $foto(5)],
    ];
    $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');
@endphp

<div>
    @if ($variant === 'A')
        @include('prototype.premium-a')
    @elseif ($variant === 'B')
        @include('prototype.premium-b')
    @elseif ($variant === 'C')
        @include('prototype.premium-c')
    @else
        @include('prototype.home-sekarang')
    @endif
</div>
