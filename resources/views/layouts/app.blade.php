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

    {{-- Estilos empujados desde las vistas --}}
    @stack('styles')
</head>
<body class="d-flex flex-column" style="min-height:100vh">

    <div class="flex-grow-1 d-flex flex-column">
        @auth    
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

                        {{-- ======= User dropdown ======= --}}
                        @php
                            $u = Auth::user();
                            $avatarUrl = null;
                            if ($u->foto_perfil) {
                                $avatarUrl = Str::startsWith($u->foto_perfil, ['http://', 'https://', '/storage/'])
                                    ? $u->foto_perfil
                                    : Storage::url($u->foto_perfil);
                            }
                            
                            $initial = strtoupper(mb_substr($u->name ?? 'U', 0, 1, 'UTF-8'));
                            $editHref = \Illuminate\Support\Facades\Route::has('perfil.index')
                                ? route('perfil.index')
                                : '#';
                        @endphp

                        <div class="user-dropdown-wrapper dropdown ms-2">
                            <button
                                class="btn btn-user nav-user-bg d-flex align-items-center justify-content-center p-0"
                                type="button"
                                aria-expanded="false"
                                aria-label="Menú de usuario"
                                style="width:55px;height:55px;"
                            >
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="Foto de perfil" class="rounded-circle"
                                         style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    <span class="d-inline-block text-white fw-semibold"
                                          style="line-height:40px;width:40px;text-align:center;">
                                        {{ $initial }}
                                    </span>
                                @endif
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2">
                                <li>
                                    <a class="dropdown-item" href="{{ $editHref }}">
                                        <i class="fa-solid fa-user-pen me-1"></i> Editar perfil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión
                                    </a>
                                    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </div>
                        {{-- ======= /User dropdown ======= --}}
                    </div>
                </div>
            </nav>
        @endauth

        {{-- --------------- Contenido --------------- --}}
        <main id="app" class="flex-grow-1 d-flex flex-column">
            @yield('content')
        </main>
    </div>

    {{-- ======= SweetAlert2 global ======= --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      // Success
      @if (session('success'))
        Swal.fire({
          icon: 'success',
          title: '¡Listo!',
          text: @json(session('success')),
          confirmButtonText: 'OK',
        });
      @endif

      // Error genérico
      @if (session('error'))
        Swal.fire({
          icon: 'error',
          title: 'Ups…',
          text: @json(session('error')),
          confirmButtonText: 'Entendido',
        });
      @endif

      // Errores de validación
      @if ($errors->any())
        Swal.fire({
          icon: 'warning',
          title: 'Revisa el formulario',
          html: `{!! implode('<br>', $errors->all()) !!}`,
          confirmButtonText: 'OK',
        });
      @endif
    </script>
</body>
</html>