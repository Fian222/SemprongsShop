<div class="container">
    <div class="row mt-3">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a wire:navigate href="{{ route('home') }}" class="text-dark"> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">History</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <table class="table text-center">
                <thead>
                    <tr>
                        <td>No.</td>
                        <td>Tanggal Pemesanan</td>
                        <td>Kode Pemesanan</td>
                        <td>Pesanan</td>
                        <td>Jumlah Pesanan</td>
                        <td>Status</td>
                        <td><strong>Total Harga</strong></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @forelse ($pesanan as $pesanan)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $pesanan->created_at }}</td>
                            <td>{{ $pesanan->kode_pemesanan }}</td>
                            <td>
                                <?php $pesanan_detail = \App\Models\pesanandetail::where('id_pesanan', $pesanan->id)->get(); ?>
                                @foreach ($pesanan_detail as $pesan)
                                    <img src="{{ url('build/images') }}/{{ $pesan->barang->gambar }}" class="img-fluid"
                                        width="50">
                                    {{ $pesan->barang->nama_barang }}
                                    <br>
                                @endforeach
                            </td>
                            <td>
                                <?php $pesanan_detail = \App\Models\pesanandetail::where('id_pesanan', $pesanan->id)->get(); ?>
                                @foreach ($pesanan_detail as $pesan)
                                    {{ $pesan->jumlah_pesanan }}
                                    <br>
                                @endforeach
                            </td>
                            <td>
                                @if ($pesanan->status == 1)
                                    Belum Lunas
                                @else
                                    Sudah Lunas
                                @endif
                            </td>
                            <td><strong>Rp. {{ number_format($pesanan->total_harga) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Data Kosong</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>
</div>
