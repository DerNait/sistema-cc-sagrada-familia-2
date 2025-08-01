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