<div class="container">
    <div class="row mt-3">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a wire:navigate href="{{ route('home') }}" class="text-dark"> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pesanan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @if (session()->has('message'))
                <div class="alert alert-danger">
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="table-responsive">
                <table class="table text-center">
                    <thead>
                        <tr>
                            <td>No.</td>
                            <td>Gambar</td>
                            <td>Nama Barang</td>
                            <td>Jumlah</td>
                            <td>Harga</td>
                            <td><strong>Total Harga</strong></td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        @forelse ($pesanan_details as $pesan)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>
                                    <img src="{{ url('build/images') }}/{{ $pesan->barang->gambar }}" class="img-fluid"
                                        width="200">
                                </td>
                                <td>{{ $pesan->barang->nama_barang }}</td>
                                <td>{{ $pesan->jumlah_pesanan }}</td>
                                <td>Rp. {{ number_format($pesan->barang->harga_barang) }}</td>
                                <td><strong>Rp. {{ number_format($pesan->total_harga) }}</strong></td>
                                <td>
                                    <button wire:click="destroy({{ $pesan->id }})" class="btn btn-danger"><i
                                            class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">Data Kosong</td>
                            </tr>
                        @endforelse
                        @if (!empty($pesanan))
                            <tr>
                                <td colspan="5" align="right"><strong>Total Yang Harus dibayarkan : </strong></td>
                                <td><strong>Rp. {{ number_format($pesanan->total_harga) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="5"></td>
                                <td colspan="2">
                                    <a wire:navigate href="{{ route('checkout') }}" class="btn btn-success btn-block">
                                        <i class="fas fa-arrow-right"></i> Check Out
                                    </a>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
