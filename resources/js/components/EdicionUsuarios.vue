<template>
    <div class="container py-5">
        <h2 class="mb-4 text-center">Lista de Empleados</h2>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Fecha Nacimiento</th>
                    <th>Salario</th>
                    <th>Fecha Registro</th>
                    <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(empleado, index) in empleados" :key="index">
                    <td><input type="text" class="form-control border-success" v-model="empleado.nombre" /></td>
                    <td><input type="text" class="form-control border-success" v-model="empleado.apellido" /></td>
                    <td><input type="email" class="form-control border-success" v-model="empleado.correo" /></td>
                    <td><Multiselect v-model="empleado.rol" :options="roles" placeholder="Rol" label="name" track-by="name" class="multiselect"/></td>
                    <td><input type="date" class="form-control border-success" v-model="empleado.fechaNacimiento" /></td>
                    <td><input type="number" class="form-control border-success" v-model="empleado.salario" /></td>
                    <td><input type="date" class="form-control border-success" v-model="empleado.fechaRegistro" /></td>
                    <td>
                        <button class="btn btn-success btn-sm">Guardar</button>
                    </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
    import { reactive } from 'vue'
    import Multiselect from 'vue-multiselect'
    
    const roles = [
        { name: 'Administracion' },
        { name: 'Inventario' },
        { name: 'Secretaria' },
        { name: 'Docente' },
        { name: 'Estudiante' }
    ]
    
    // Datos de prueba
    const empleados = reactive([
        {
        nombre: 'Nombre 1',
        apellido: 'Apellido 1',
        correo: 'ejemplocorreo1@gmail.edu.gt',
        rol: roles[3],
        fechaNacimiento: '1990-01-01',
        salario: 3500,
        fechaRegistro: '2025-01-01'
        },
        {
        nombre: 'Nombre 2',
        apellido: 'Apellido 2',
        correo: 'ejemplocorreo2@gmail.edu.gt',
        rol: roles[2],
        fechaNacimiento: '1992-02-02',
        salario: 4000,
        fechaRegistro: '2025-02-02'
        },
    ])
</script>

<style scoped>
    .multiselect {
        border: 1px solid #198754;
        border-radius: 4px;
        padding: 0.375rem 0.75rem;
        min-height: 38px;
    }
</style>
