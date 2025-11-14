<template>
  <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="bg-white px-6 py-3 rounded shadow d-flex flex-column justify-content-center align-items-center"
         style="width: 600px; text-align: center; min-height: 500px;">
      <img src="/images/logo_.jpg" alt="Logo" class="mb-4 w-75" style="margin-top: 50px;" />
      <div class="mt-5 w-100 d-flex flex-column align-items-center">
        <h3 class="mb-4">Registro</h3>

        <form class="row needs-validation g-3 w-100"
              ref="formElement"
              @submit.prevent="handleSubmit"
              novalidate
              style="max-width: 450px;">
          
          <!-- Nombre -->
          <div class="col-12">
            <label for="nombre" class="form-label fs-5">Nombre</label>
            <input type="text" class="form-control" id="nombre" v-model="form.nombre"/>
            <div class="invalid-feedback">Por favor ingresa tu nombre.</div>
            <div class="valid-feedback">¡Lindo nombre!</div>
          </div>

          <!-- Apellido -->
          <div class="col-12">
            <label for="apellido" class="form-label fs-5">Apellido</label>
            <input type="text" class="form-control" id="apellido" v-model="form.apellido"/>
            <div class="invalid-feedback">Por favor ingresa tu apellido.</div>
            <div class="valid-feedback"></div>
          </div>

          <!-- Correo -->
          <div class="col-12">
            <label for="correo" class="form-label fs-5">Correo</label>
            <input type="email" class="form-control" id="correo" v-model="form.correo"/>
            <div class="invalid-feedback">Por favor ingresa un correo válido.</div>
            <div class="valid-feedback">¡Se ve bien!</div>
          </div>

          <!-- Contraseña -->
          <div class="col-12">
            <label for="contraseña" class="form-label fs-5">Contraseña</label>
            <input type="password" class="form-control" id="contraseña" v-model="form.contraseña" minlength="6"  />
            <div class="invalid-feedback">Debe tener al menos 6 caracteres.</div>
            <div class="valid-feedback">¡Mantenla en secreto!</div>
          </div>

          <!-- Confirmar contraseña -->
          <div class="col-12">
            <label for="confirmarContraseña" class="form-label fs-5">Confirmar Contraseña</label>
            <input type="password" class="form-control" id="confirmarContraseña" v-model="form.confirmarContraseña"
                   :class="{'is-invalid': !passwordsCoinciden, 'is-valid': form.confirmarContraseña && passwordsCoinciden}"
                    />
            <div class="invalid-feedback">Las contraseñas no coinciden.</div>
            <div class="valid-feedback">¡Si coinciden!</div>
          </div>

          <!-- Fecha de nacimiento -->
          <div class="col-12">
            <label for="fechaNacimiento" class="form-label fs-5">Fecha de nacimiento</label>
            <input type="date" class="form-control" id="fechaNacimiento" v-model="form.fechaNacimiento"/>
            <div class="invalid-feedback">Por favor ingresa tu fecha de nacimiento.</div>
            <div class="valid-feedback">¡Se ve bien!</div>
          </div>

          <!-- Rol -->
          <div class="col-12">
            <label class="form-label fs-5">Rol</label>
            <Multiselect v-model="form.rol"
                         :options="roles"
                         placeholder="Selecciona un rol"
                         label="name"
                         track-by="name"
                         :class="{'is-invalid': showValidation && !form.rol, 'is-valid': form.rol}"
                         class="multiselect" />
            <div v-if="showValidation && !form.rol" class="invalid-feedback d-block">Por favor selecciona un rol.</div>
            <div v-if="form.rol" class="valid-feedback d-block">¡Se ve bien!</div>
          </div>

          <!-- Botón -->
          <div class="col-12">
            <button type="submit" class="btn btn-success w-100" :disabled="enviado">Registrar Usuario</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import Multiselect from 'vue-multiselect'
import Swal from 'sweetalert2';
const form = reactive({
  nombre: '',
  apellido: '',
  correo: '',
  contraseña: '',
  confirmarContraseña: '',
  fechaNacimiento: '',
  rol: null
})

const enviado = ref(false)
const formElement = ref(null)
const showValidation = ref(false)

const roles = [
  { name: 'Administracion' },
  { name: 'Inventario' },
  { name: 'Secretaria' },
  { name: 'Docente' },
  { name: 'Estudiante' }
]

const passwordsCoinciden = computed(() => form.contraseña === form.confirmarContraseña)

const handleSubmit = (event) => {
  const formEl = event.target

  if (!formEl.checkValidity()) {
    event.preventDefault()
    event.stopPropagation()

    // 🔴 Aquí se muestra el SweetAlert si el formulario no es válido
    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: "¡Por favor completa todos los campos correctamente!",
      footer: '<a href="#">¿Por qué tengo este error?</a>'
    })
  } else {
    enviado.value = true
    console.log('Formulario válido, enviando...', formEl)
    // Aquí iría tu lógica real para enviar el formulario
  }

  formEl.classList.add('was-validated')
}

</script>

<style scoped>
body {
  font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, sans-serif; 
}
.multiselect {
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  padding: 0.375rem 0.75rem;
  min-height: 38px;
}
.is-invalid {
  border-color: #dc3545 !important;
}
.is-valid {
  border-color: #198754 !important;
}
.valid-feedback,
.invalid-feedback {
  display: block;
}
</style>
