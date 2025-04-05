<template>
    <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
        <div class="bg-white px-4 py-5 rounded shadow d-flex flex-column justify-content-center align-items-center"
        style="width: 550px; text-align: center; min-height: 600px;">
            <img src="/public/images/logo_.jpg" alt="Logo" class="mb-4 w-75" style="margin-top: 50px;" />
            <div class="mt-9 w-100 d-flex flex-column align-items-center" style="margin-top: 50px;">
                <h3 class="mb-4">Registro</h3>
                <input type="text" class="form-control mb-3 border-success mx-auto" style="max-width: 400px;" placeholder="Nombre" v-model="form.nombre"/>
                <input type="text" class="form-control mb-3 border-success mx-auto" style="max-width: 400px;" placeholder="Apellido" v-model="form.apellido"/>
                <input type="email" class="form-control mb-3 border-success mx-auto" style="max-width: 400px;" placeholder="Correo" v-model="form.correo"/>
                <input type="password" class="form-control mb-3 border-success mx-auto" style="max-width: 400px;" placeholder="Contraseña" v-model="form.contraseña"/>
                <input type="password" class="form-control mb-3 border-success mx-auto" style="max-width: 400px;" placeholder="Confirmar Contraseña" v-model="form.confirmarContraseña"/>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'

const form = reactive({
    nombre: '',
    apellido: '',
    correo: '',
    contraseña: '',
    confirmarContraseña: ''
})
</script>
