<div class="container">
    <div class="row mt-3">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a wire:navigate href="{{ route('home') }}" class="text-dark"> Home</a></li>
                    <li class="breadcrumb-item"><a wire:navigate href="{{ route('pesanann') }}" class="text-dark"> Pesanan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Check Out</li>
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
            <a wire:navigate href="{{ route('pesanann') }}" class="btn btn-sm btn-dark"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col">
            <h4>Informasi Pembayaran</h4>
            <hr>
            <p>Untuk Pembayaran Silakan Dapat Transfer di Rekening di bawah ini sebesar : <br><strong>Rp.
                    {{ number_format($total_harga) }}</strong></p>
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-building-columns fa-3x" aria-hidden="true"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5>Bank BCA</h5>
                    No. Rekening 12722993 atas nama <strong>Akhmat Oktafian</strong>
                </div>
            </div>
        </div>
        <div class="col">
            <h4>Informasi Pengiriman</h4>
            <hr>
            <form wire:submit.prevent="checkout">
                <div class="form-group">
                    <label for="">No. HP</label>
                    <input type="tel" id="nohp" class="form-control @error('nohp') is-invalid @enderror"
                        wire:model="nohp" value="{{ old('nohp') }}" autocomplete="name" autofocus
                        pattern="(\+62|62|0)8[1-9][0-9]{9,10}$">
                    @error('nohp')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label for="">Alamat</label>
                    <textarea wire:model="alamat" class="form-control @error('alamat') is-invalid @enderror"></textarea>
                    @error('alamat')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success btn-block mt-3 col-lg-12"><i
                        class="fas fa-arrow-right"></i>
                    Checkout</button>
            </form>
        </div>
    </div>
</div>
