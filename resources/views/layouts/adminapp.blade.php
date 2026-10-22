<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Halaman Administrator</title>
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="{{ asset('back/css/styles.css') }}" rel="stylesheet" />
    <link href="{{ asset('back/css/customadmin.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}">
</head>

<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar-->
        <div class="border-end bg-white" id="sidebar-wrapper">
            <div class="sidebar-heading border-bottom bg-light">Semprong Loves</div>
            <div class="list-group list-group-flush">
                <a wire:navigate class="list-group-item list-group-item-action list-group-item-light p-3 {{ request()->is('admin/dashboard') ? 'bg-secondary text-white' : '' }}"
                    href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a wire:navigate class="list-group-item list-group-item-action list-group-item-light p-3 {{ request()->is('admin/pesananmasuk') ? 'bg-secondary text-white' : '' }}"
                    href="{{ route('admin.pesanan') }}">Pesanan</a>
                <a wire:navigate class="list-group-item list-group-item-action list-group-item-light p-3 {{ Request::path() === 'admin/riwayatpesanan' ? 'bg-secondary text-white' : '' }}"
                    href="{{ route('admin.riwayat') }}">Riwayat Pesanan</a>
                <a wire:navigate class="list-group-item list-group-item-action list-group-item-light p-3 {{ Request::path() === 'admin/produkadmin' ? 'bg-secondary text-white' : '' }}"
                    href="{{ route('admin.produk') }}">Produk</a>
            </div>
        </div>

        <!-- Page content wrapper-->
        <div id="page-content-wrapper">
            <!-- Top navigation-->
            @section('header')
                @include('layouts.back.headeradmin')
            @show
            <!-- Page content-->
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>
    <!-- Bootstrap core JS-->
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" data-navigate-once></script>
    <!-- Core theme JS-->
    <script src="{{ asset('back/js/scripts.js') }}" data-navigate-once></script>
</body>

</html>
