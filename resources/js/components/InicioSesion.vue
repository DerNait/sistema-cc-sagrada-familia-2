<template>
  <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div
      class="bg-white px-4 py-5 rounded shadow d-flex flex-column justify-content-center align-items-center"
      style="width: 550px; text-align: center; min-height: 600px;"
    >
      <img
        src="/images/logo_.jpg"
        alt="Logo"
        class="mb-4 w-75"
        style="margin-top: 50px;"
      />

      <div class="mt-5 w-100 d-flex flex-column align-items-center" style="margin-top: 85px;">
        <h3 class="mb-4">Inicio de Sesión</h3>

        <!-- USUARIO -->
        <div class="mb-3 text-start w-100" style="max-width: 400px;">
          <label for="usuario" class="form-label">Usuario</label>
          <input
            type="text"
            id="usuario"
            class="form-control border-success"
            v-model="form.usuario"
          />
        </div>

        <!-- CONTRASEÑA -->
        <div class="mb-4 text-start w-100" style="max-width: 400px;">
          <label for="contraseña" class="form-label">Contraseña</label>
          <input
            type="password"
            id="contraseña"
            class="form-control border-success"
            v-model="form.contraseña"
          />
        </div>

        <!-- BOTÓN -->
        <button class="btn btn-success w-100" style="max-width: 400px;">Iniciar sesión</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const form = reactive({
  usuario: '',
  contraseña: ''
})
</script>
