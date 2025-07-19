<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Fuentes + Vite --}}
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    @vite(['resources/sass/app.scss','resources/js/app.js'])
</head>
<body class="d-flex flex-column" style="min-height:100vh">

    <div class="flex-grow-1 d-flex flex-column">

        {{-- --------------- Navbar --------------- --}}
        <nav class="custom-navbar">
            <div class="d-flex align-items-center gap-3 w-100">
                {{-- Logo --}}
                <a href="{{ route('dashboard.index') }}" class="navbar-logo">
                    <img src="{{ asset('images/logo-sagrada-familia.png') }}" alt="Logo" height="60">
                </a>

                {{-- Contenedor del menú + botón user --}}
                <div class="nav-bubble d-flex flex-grow-1 align-items-center justify-content-end">
                    {{-- Menú principal --}}
                    <div class="navbar-bg">
                        {!! \App\Navigation\Navbar::render() !!}
                    </div>

                    {{-- Botón de perfil (por ahora vacío) --}}
                    <button class="btn btn-user nav-user-bg ms-2" type="button">
                        <i class="fa-regular fa-user fa-lg"></i>
                    </button>
                </div>
            </div>
        </nav>

        {{-- --------------- Contenido --------------- --}}
        <main id="app" class="flex-grow-1">
            @yield('content')
        </main>

    </div>
</body>
</html>