@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="bg-white px-6 py-3 rounded shadow d-flex flex-column justify-content-center align-items-center"
        style="width: 600px; text-align: center; min-height: 500px;">
        
        <img src="/images/logo_.jpg" alt="Logo" class="mb-4 w-75" style="margin-top: 50px;" />

        <div class="mt-5 w-100 d-flex flex-column align-items-center" style="margin-top: 50px;">
            <h3 class="mb-4">Registro</h3>

            <form method="POST" action="{{ route('register') }}" class="w-100 d-flex flex-column align-items-center">
                @csrf

                <!-- NOMBRE -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="name" class="form-label">Nombre</label>
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
                    <label for="apellido" class="form-label">Apellido</label>
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
                    <label for="email" class="form-label">Correo</label>
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
                    <label for="password" class="form-label">Contraseña</label>
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
                    <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                    <input type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control border-success"
                        required
                        autocomplete="new-password">
                </div>

                <!-- FECHA DE NACIMIENTO -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
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
                    <label for="role" class="form-label">Rol</label>
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
                        <label for="salario" class="form-label">Salario Base</label>
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

                    <!---Fecha de Registro-->
                    <div class="mb-3 text-start w-100" style="max-width: 450px;">
                        <label for="fecha_registro" class="form-label">Fecha de Registro</label>
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
                <button type="submit" class="btn btn-success w-100" style="max-width: 450px;">
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
            
            
            console.log("Rol seleccionado:", rolNombre);
            console.log("¿Es rol de empleado?", rolesEmpleado.includes(rolNombre));
            
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
