<template>
    <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
        <div
        class="bg-white px-6 py-3 rounded shadow d-flex flex-column justify-content-center align-items-center"
        style="width: 600px; text-align: center; min-height: 500px;">
            <img
            src="/images/logo_.jpg"
            alt="Logo"
            class="mb-4 w-75"
            style="margin-top: 50px;"
            />

            <div class="mt-5 w-100 d-flex flex-column align-items-center" style="margin-top: 50px;">
                <h3 class="mb-4">Registro</h3>

                <!-- NOMBRE -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" id="nombre" class="form-control border-success" v-model="form.nombre" />
                </div>
        
                <!-- APELLIDO -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" id="apellido" class="form-control border-success" v-model="form.apellido" />
                </div>
  
                <!-- CORREO -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="correo" class="form-label">Correo</label>
                    <input type="email" id="correo" class="form-control border-success" v-model="form.correo" />
                </div>
  
                <!-- CONTRASEÑA -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="contraseña" class="form-label">Contraseña</label>
                    <input type="password" id="contraseña" class="form-control border-success" v-model="form.contraseña" />
                </div>

                <!-- CONFIRMAR CONTRASEÑA -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="confirmarContraseña" class="form-label">Confirmar Contraseña</label>
                    <input type="password" id="confirmarContraseña" class="form-control border-success" v-model="form.confirmarContraseña" />
                </div>

                <!-- FECHA DE NACIMIENTO -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                    <label for="fechaNacimiento" class="form-label">Fecha de nacimiento</label>
                    <input type="date" id="fechaNacimiento" class="form-control border-success" v-model="form.fechaNacimiento" />
                </div>

                <!-- ROL -->
                <div class="mb-3 text-start w-100" style="max-width: 450px;">
                <label for="rol" class="form-label">Rol</label>
                <Multiselect v-model="form.rol" :options="roles" placeholder="Selecciona un rol" label="name" track-by="name"  class="multiselect"/>
                </div>
            
            <!-- BOTÓN -->
            <button class="btn btn-success w-100" style="max-width: 450px;" @click="registrarUsuario" :disabled='enviado'>Registrar Usuario</button>
        </div>
    </div>
</div>
</template>

<script setup>
import { reactive } from 'vue'
import Multiselect from 'vue-multiselect'
import axis from 'axios'


const form = reactive({
    nombre: '',
    apellido: '',
    correo: '',
    contraseña: '',
    confirmarContraseña: '',
    fechaNacimiento: '',
    rol: null
})

const enviando = ref(false)

const roles = [
  { name: 'Administracion' },
  { name: 'Inventario' },
  { name: 'Secretaria' },
  { name: 'Docente' },
  { name: 'Estudiante' }
]


const roles = [
    { name: 'Administracion' },
    { name: 'Inventario' },
    { name: 'Secretaria' },
    { name: 'Docente' },
    { name: 'Estudiante' }
    ]
</script>

<style scoped>
.multiselect {
  border: 1px solid #198754; /* color borde success */
  border-radius: 4px;
  padding: 0.375rem 0.75rem;
  min-height: 38px;
}
</style>
