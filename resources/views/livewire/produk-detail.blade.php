<div class="container">
    <div class="row mt-3">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a wire:navigate href="{{ route('home') }}" class="text-dark"> Home</a></li>
                    <li class="breadcrumb-item"><a wire:navigate href="{{ route('produk') }}" class="text-dark"> Produk</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Produk detail</li>
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

    <div class="row">
        <div class="col-md-6">
            <div class="card gambar-produk">
                <div class="card-body">
                    <img src="{{ url('build/images') }}/{{ $product->gambar }}" class="img-fluid">
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <h2><strong>{{ $product->nama_barang }}</strong>
                @if ($product->stok_barang >= 1)
                    <span class="badge text-bg-success"><i class="fas fa-check"></i> Tersedia</span>
                @else
                    <span class="badge text-bg-danger"><i class="fas fa-times"></i> Stok Habis</span>
                @endif
            </h2>
            <h4>Rp. {{ number_format($product->harga_barang) }}</h4>
            <hr>
            <div class="row">
                <div class="col">
                    <form wire:submit.prevent="masukkankeranjang">
                        <table class="table" style="border-top: hidden">
                            <tr>
                                <td>Varian Rasa</td>
                                <td>:</td>
                                <td>{{ $product->nama_barang }}</td>
                            </tr>
                            <tr>
                                <td>Jumlah Barang yang tersedia</td>
                                <td>:</td>
                                <td>{{ $product->stok_barang }}</td>
                            </tr>
                            <tr>
                                <td>Jumlah yang ingin dipesan</td>
                                <td>:</td>
                                <td>
                                    <input id="jumlah_pesanan" type="number"
                                        class="form-control @error('jumlah_pesanan') is-invalid @enderror"
                                        wire:model="jumlah_pesanan" value="{{ old('jumlah_pesanan') }}" required
                                        autocomplete="jumlah_pesanan" autofocus>

                                    @error('jumlah_pesanan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <button type="submit" class="btn btn-dark col-lg-12"
                                        @if ($product->stok_barang < 1) disabled @endif><i
                                            class="fas fa-shopping-cart"></i> Masukkan Ke Pesanan</button>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
