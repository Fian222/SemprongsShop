<div class="container">

    {{-- banner --}}
    <div class="banner text-center mt-4">
        <img src="{{ asset('images/banner-semprong.jpg') }}" alt="Kue semprong di atas piring"
            class="w-100 rounded shadow" style="height: 360px; object-fit: cover;">
        <small class="d-block text-muted text-end mt-1">
            Foto: <a href="https://commons.wikimedia.org/wiki/File:Kuih_Kapit_(53564062798).jpg" class="text-muted"
                target="_blank" rel="noopener">Choo Yut Shing</a>, CC BY 2.0
        </small>
    </div>

    {{-- product --}}
    <section class="produk mt-5">
        <h1>
            <strong>Produk Terbaik</strong>
            <a href="{{ route('produk') }}" class="btn btn-dark lihat"><i class="fas fa-eye"></i> Lihat Semua</a>
        </h1>
        <div class="row">
            @foreach ($barangs as $barang)
                <div class="col">
                    <div class="card shadow mt-5">
                        <div class="card-body text-center">
                            <img src="{{ url('build/images') }}/{{ $barang->gambar }}">
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <h5><strong>{{ $barang->nama_barang }}</strong></h5>
                                    <h5>Rp. {{ number_format($barang->harga_barang) }}</h5>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <a href="{{ route('produk.detail', $barang->id) }}"
                                        class="btn btn-dark btn-block w-100"><i class="fa fa-eye"></i> Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
