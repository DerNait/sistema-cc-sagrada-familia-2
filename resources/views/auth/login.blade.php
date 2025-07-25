@extends('layouts.app')

@section('content')
<div class="login-background d-flex justify-content-center align-items-center min-vh-100">
    <div class="bg-white px-4 py-5 rounded shadow d-flex flex-column justify-content-center align-items-center"
        style="width: 550px; text-align: center; min-height: 600px;">
        
        <img src="/images/logo_.jpg" alt="Logo" class="mb-4 w-75" style="margin-top: 50px;" />

        <div class="mt-5 w-100 d-flex flex-column align-items-center" style="margin-top: 85px;">
            <h3 class="mb-4">Inicio de Sesión</h3>

            <form method="POST" action="{{ route('login') }}" class="needs-validation w-100" style="max-width: 400px;" novalidate>
                @csrf

                <!-- USUARIO -->
                <div class="mb-3 text-start w-100" style="max-width: 400px;">
                    <label for="email" class="form-label">Usuario</label>
                    <input type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        autofocus
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- CONTRASEÑA -->
                <div class="mb-4 text-start w-100" style="max-width: 400px;">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        autocomplete="current-password"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <input type="hidden" name="remember" id="remember" value="1">

                <!-- BOTÓN -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn custom-btn w-100" style="max-width: 400px;">
                        Iniciar sesión
                    </button>

                    @if (Route::has('password.request'))
                        <a class="btn btn-link ps-0 mt-2 custom-link" href="{{ route('password.request') }}">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.login-background {
    position: relative;
     background-image: url("{{ asset('images/colegio_fondo.jpg') }}"); 
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
    z-index: 1;
}

.login-background::before{
    content: "";
    position: absolute;
    top 0;
    left 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 2;
}

    .login-background > * {
    position: relative;
    z-index: 3; 
    }

.custom-btn {
    background-color: #83AA6B;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.custom-btn:hover {
    background-color: #6f9658;
}

.custom-link {
    color: #83AA6B;
    text-decoration: underline;
    transition: color 0.3s ease;
}

.custom-link:hover {
    color: #6f9658;
    text-decoration: underline;
}
</style>
@endpush

@php
    $hideFooter = true; // Esto ocultará el footer
@endphp
