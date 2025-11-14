@extends('layouts.app')

@section('content')
<div class="login-background d-flex justify-content-center align-items-center min-vh-100">
    <div class="bg-white px-6 py-3 rounded shadow d-flex flex-column justify-content-center align-items-center"
        style="width: 600px; text-align: center; min-height: 500px; position: relative; z-index: 3;">
        
        <img src="/images/logo_.jpg" alt="Logo" class="mb-4 w-75" style="margin-top: 50px;" />

        <div class="mt-5 w-100 d-flex flex-column align-items-center" style="margin-top: 50px;">
            <h3 class="mb-4">Registro</h3>

            <form method="POST" action="{{ route('register') }}" class="w-100 d-flex flex-column align-items-center">
                @csrf

                <!-- NOMBRE -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="name" class="form-label fs-5">Nombre</label>
                    <input type="text"
                        id="name"
                        name="name"
                        class="form-control border-success @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        required
                        autocomplete="name"
                        autofocus>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- APELLIDO -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="apellido" class="form-label fs-5">Apellido</label>
                    <input type="text"
                        id="apellido"
                        name="apellido"
                        class="form-control border-success @error('apellido') is-invalid @enderror"
                        value="{{ old('apellido') }}"
                        required
                        autocomplete="family-name">
                    @error('apellido')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- CORREO -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="email" class="form-label fs-5">Correo</label>
                    <input type="email"
                        id="email"
                        name="email"
                        class="form-control border-success @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- CONTRASEÑA -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="password" class="form-label fs-5">Contraseña</label>
                    <input type="password"
                        id="password"
                        name="password"
                        class="form-control border-success @error('password') is-invalid @enderror"
                        required
                        autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- CONFIRMAR CONTRASEÑA -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="password_confirmation" class="form-label fs-5">Confirmar Contraseña</label>
                    <input type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control border-success"
                        required
                        autocomplete="new-password">
                </div>

                <!-- FECHA DE NACIMIENTO -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="fecha_nacimiento" class="form-label fs-5">Fecha de nacimiento</label>
                    <input type="date"
                        id="fecha_nacimiento"
                        name="fecha_nacimiento"
                        class="form-control border-success @error('fecha_nacimiento') is-invalid @enderror"
                        value="{{ old('fecha_nacimiento') }}"
                        required>
                    @error('fecha_nacimiento')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- ROL -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="role" class="form-label fs-5">Rol</label>
                    <select name="role" id="role"
                        class="form-control border-success @error('role') is-invalid @enderror"
                        required>
                        <option value="">Selecciona un rol</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}"
                                data-nombre="{{ strtolower($rol->nombre) }}"
                                {{ old('role') == $rol->id ? 'selected' : '' }}>
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- CAMPOS ADICIONALES PARA EMPLEADOS -->
                <div id="camposEmpleado" style="display: none; width: 100%; max-width: 450px;">
                    <!-- SALARIO -->
                    <div class="mb-3 text-start">
                        <label for="salario" class="form-label fs-5">Salario Base</label>
                        <input type="number"
                            id="salario"
                            name="salario"
                            class="form-control border-success @error('salario') is-invalid @enderror"
                            value="{{ old('salario') }}">
                        @error('salario')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Fecha de Registro -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="fecha_registro" class="form-label fs-5">Fecha de Registro</label>
                    <input type="date"
                        id="fecha_registro"
                        name="fecha_registro"
                        class="form-control border-success @error('fecha_registro') is-invalid @enderror"
                        value="{{ old('fecha_registro') }}">
                    @error('fecha_registro')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- BOTÓN -->
                <button type="submit" class="btn custom-btn w-100" style="max-width: 450px;">
                    Registrar Usuario
                </button>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPT DE CAMBIO DE CAMPOS -->
<script>
    function mostrarCamposEmpleado() {
        const select = document.getElementById('role');
        const campos = document.getElementById('camposEmpleado');
        
        if (select.selectedIndex > 0) {
            const rolNombre = select.options[select.selectedIndex].getAttribute('data-nombre');
            const rolesEmpleado = ['docente', 'secretaria', 'inventario'];
            
            campos.style.display = rolesEmpleado.includes(rolNombre) ? 'block' : 'none';
        } else {
            campos.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        mostrarCamposEmpleado();
        document.getElementById('role').addEventListener('change', mostrarCamposEmpleado);
    });
</script>
@endsection

@push('styles')
<style>
.login-background {
    position: relative;
    background-image: url('/images/colegio_fondo.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
    z-index: 1;
}

.login-background::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 2;
}

.login-background > * {
    position: relative;
    z-index: 3;
}

/* Botón personalizado */
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


input.form-control,
select.form-control,
textarea.form-control {
    border-color: #83AA6B !important;
    box-shadow: none !important;
    transition: border-color 0.3s ease;
}

input.form-control:focus,
select.form-control:focus,
textarea.form-control:focus {
    border-color: #6f9658 !important;
    box-shadow: 0 0 0 0.2rem rgba(131, 170, 107, 0.25) !important;
    outline: none;
}
</style>
@endpush

@php
    $hideFooter = true; // Esto ocultará el footer
@endphp
