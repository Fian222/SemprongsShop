<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Semprong</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href='https://fonts.googleapis.com/css?family=Josefin Sans' rel='stylesheet'>

    @livewireStyles

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="/bootstrap/bootstrap-5.2.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="build/assets/custom.css">
    <script src="/bootstrap/bootstrap-5.2.2-dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <style>
        html {
            height: 100%;
            box-sizing: border-box;
        }

        body {
            min-height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            margin: 0;
            padding-bottom: 3.74rem;
            box-sizing: inherit;
        }
    </style>
</head>

<body>
    <div id="app">

        <livewire:navbar />
        <main class="py-2">
            @yield('content')
            @include('layouts.footer')
        </main>
    </div>

    @livewireScripts
</body>

</body>

</html>
