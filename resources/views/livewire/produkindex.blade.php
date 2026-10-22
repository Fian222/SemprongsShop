<div class="container">
    <div class="row mt-3">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a wire:navigate href="{{ route('home') }}" class="text-dark"> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Produk</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <h2>List <strong>Produk</strong></h2>
        </div>
        <div class="col-md-4">
            <div class="input-group mb-3">
                <input wire:model.live="search" type="text" class="form-control" placeholder="Cari" aria-label="Cari"
                    aria-describedby="basic-addon1">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>
    </div>

    <section class="produk mb-5">
        <div class="row">
            @foreach ($barangs as $barang)
                <div class="col-md-4">
                    <div class="card shadow mt-5 col">
                        <div class="card-body text-center">
                            <img src="{{ url('build/images') }}/{{ $barang->gambar }}" class="img-fluid">
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <h5><strong>{{ $barang->nama_barang }}</strong></h5>
                                    <h5>Rp. {{ number_format($barang->harga_barang) }}</h5>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <a wire:navigate href="{{ route('produk.detail', $barang->id) }}"
                                        class="btn btn-dark btn-block w-100"><i class="fa fa-eye"></i> Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row mt-3">
            <div class="container">
                {{ $barangs->links() }}
            </div>
        </div>
    </section>
</div>
