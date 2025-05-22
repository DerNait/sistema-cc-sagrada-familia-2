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
    <nav class="navbar navbar-expand-md navbar-light bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand text-light" href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @guest
                        <li><a class="nav-link text-light" href="{{ route('login') }}">Login</a></li>
                        <li><a class="nav-link text-light" href="{{ route('register') }}">Register</a></li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" data-bs-toggle="dropdown">{{ Auth::user()->name }}</a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a>
                                <form id="logout-form" method="POST" action="{{ route('logout') }}">@csrf</form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <div class="flex-grow-1 d-flex flex-column">

        {{-- --------------- Navbar horizontal --------------- --}}
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
            {!! \App\Navigation\Navbar::render() !!}
        </nav>

        {{-- --------------- Contenido --------------- --}}
        <main id="app" class="flex-grow-1 p-3">
            @yield('content')
        </main>

    </div>

        {{-- Footer fijo con navegación --}}
    @unless(isset($hideFooter) && $hideFooter)    
    <footer class="bg-white border-top shadow-sm py-2">
        <div class="container">
            <div class="d-flex justify-content-around text-center small">
                <div><span>🏠</span><br>Home</div>
                <div><span>💰</span><br>Pagos</div>
                <div><span>⚠️</span><br>Notificaciones</div>
            </div>
        </div>
    </footer>
    @endunless
</body>
</html>