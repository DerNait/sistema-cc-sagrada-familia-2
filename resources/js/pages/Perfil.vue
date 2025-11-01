<template>
  <div class="center-container">
    <div class="d-flex flex-column justify-content-center align-items-center">
      <div class="d-flex justify-content-center my-5">
        <h1 class="fw-bold">
          <i class="fas fa-user text-primary me-2"></i>
          Perfil de Usuario
        </h1>
      </div>
      <div class="card shadow-lg border-0 bg-light w-100 mb-4" style="max-width: 880px">
        <div class="card-body">

          <!-- Fila: Foto + Nombre -->
          <div class="row align-items-center g-3">
            <div class="col-auto d-flex flex-column align-items-center mx-2">
              <div class="position-relative profile-photo-wrapper" @mouseenter="hoverPhoto=true" @mouseleave="hoverPhoto=false">
                <img
                  v-if="user.foto_perfil"
                  :src="user.foto_perfil"
                  class="rounded-circle object-fit-cover border"
                  :alt="`Foto de ${user.name}`"
                  width="112"
                  height="112"
                />
                <div class="rounded-circle border bg-secondary d-flex align-items-center justify-content-center"
                  v-else
                  style="width: 112px; height: 112px;"
                >
                  <p class="fs-1 text-white m-0 p-0">
                    {{ user.name.charAt(0).toUpperCase() + (user.apellido ? user.apellido.charAt(0).toUpperCase() : '') }}
                  </p>
                </div>
                <!-- Overlay hover -->
                <button
                  type="button"
                  class="btn overlay-btn d-flex flex-column align-items-center justify-content-center"
                  @click="triggerFile"
                  :class="{ 'show-overlay': hoverPhoto }"
                  aria-label="Cambiar foto"
                >
                  <i class="fas fa-pen fa-lg mb-1"></i>
                  <small>Cambiar</small>
                </button>
                <input
                  ref="fileInput"
                  type="file"
                  class="d-none"
                  accept="image/*"
                  @change="onFileChange"
                />
              </div>
              <!-- Acciones foto -->
              <div class="mt-2 d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" @click="triggerFile">
                  <i class="fas fa-upload me-1"></i> Subir
                </button>
                <button
                  class="btn btn-sm btn-outline-danger"
                  @click="removePhoto"
                  :disabled="isUploading || (!user.foto_perfil && !photoPreview)"
                >
                  <i class="fas fa-trash me-1"></i> Quitar
                </button>
              </div>
              <!-- Errores/Sucesos foto -->
              <div class="mt-2">
                <div v-if="photoError" class="text-danger small"><i class="fas fa-circle-exclamation me-1"></i>{{ photoError }}</div>
                <div v-if="photoSuccess" class="text-primary small"><i class="fas fa-check-circle me-1"></i>{{ photoSuccess }}</div>
              </div>
            </div>

            <div class="col">
              <div class="d-flex flex-column">
                <h2 class="fw-semibold mb-1">{{ user.name }} {{ user.apellido }}</h2>
                <div class="text-muted">
                  <h5>
                    <i class="fas fa-envelope me-2"></i>{{ user.email }}
                  </h5>
                </div>
              </div>
            </div>
          </div>

          <hr class="my-4" />

          <!-- Más información (solo lectura) -->
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label text-muted fw-semibold fs-5 mb-0">Rol</label>
              <div class="form-control-plaintext p-0">
                {{ user.role?.nombre || '—' }}
              </div>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label text-muted fw-semibold fs-5 mb-0">Fecha de registro</label>
              <div class="form-control-plaintext p-0">
                {{ user.fecha_registro || user.created_at || '—' }}
              </div>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label text-muted fw-semibold fs-5 mb-0">Fecha de nacimiento</label>
              <div class="form-control-plaintext p-0">
                {{ user.fecha_nacimiento || '—' }}
              </div>
            </div>
          </div>

          <hr class="my-4" />

          <!-- Cambiar contraseña -->
          <form @submit.prevent="submitPassword" novalidate>
            <h4 class="mb-3"><i class="fas fa-key me-2 text-warning"></i>Cambiar contraseña</h4>

            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label text-muted fw-semibold fs-5">Nueva contraseña</label>
                <div class="input-group">
                  <input
                    :type="showPass ? 'text' : 'password'"
                    class="form-control"
                    v-model.trim="passwordForm.password"
                    :class="{ 'is-invalid': passErrors.password }"
                    autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
                  />
                  <button
                    type="button"
                    class="btn btn-outline-secondary"
                    @click="showPass = !showPass"
                    :aria-pressed="showPass"
                  >
                    <i :class="showPass ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                  </button>
                  <div class="invalid-feedback">{{ passErrors.password }}</div>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label text-muted fw-semibold fs-5">Confirmar contraseña</label>
                <input
                  :type="showPass ? 'text' : 'password'"
                  class="form-control"
                  v-model.trim="passwordForm.password_confirmation"
                  :class="{ 'is-invalid': passErrors.password_confirmation }"
                  autocomplete="new-password"
                  placeholder="Repite tu contraseña"
                />
                <div class="invalid-feedback">{{ passErrors.password_confirmation }}</div>
              </div>

              <div class="col-12">
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="isChangingPass"
                >
                  <i v-if="isChangingPass" class="fas fa-spinner fa-spin me-2"></i>
                  Guardar contraseña
                </button>

                <span v-if="passSuccess" class="text-success ms-3">
                  <i class="fas fa-check-circle me-1"></i>{{ passSuccess }}
                </span>
                <span v-if="passError && !passSuccess" class="text-danger ms-3">
                  <i class="fas fa-circle-exclamation me-1"></i>{{ passError }}
                </span>
              </div>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

// --- Foto de perfil ---
const fileInput = ref(null)
const hoverPhoto = ref(false)
const photoPreview = ref(null)
const isUploading = ref(false)
const photoError = ref('')
const photoSuccess = ref('')

function triggerFile() {
  photoError.value = ''
  photoSuccess.value = ''
  fileInput.value?.click()
}

async function onFileChange(e) {
  photoError.value = ''
  photoSuccess.value = ''
  const file = e.target.files?.[0]
  if (!file) return

  const maxSize = 2 * 1024 * 1024 // 2MB
  if (!file.type.startsWith('image/')) {
    photoError.value = 'El archivo debe ser una imagen.'
    e.target.value = ''
    return
  }
  if (file.size > maxSize) {
    photoError.value = 'La imagen no debe superar los 2MB.'
    e.target.value = ''
    return
  }

  // Preview inmediata (opcional)
  photoPreview.value = URL.createObjectURL(file)

  try {
    isUploading.value = true

    // 1) Sube el archivo
    const fd = new FormData()
    fd.append('file', file)
    fd.append('path', 'uploads/perfiles') // carpeta destino en 'public'

    const up = await axios.post('/upload', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    // 2) Actualiza el perfil con el path devuelto
    const path = up.data.path // 'uploads/perfiles/xxx.jpg'
    const res = await axios.put('/perfil', { foto_perfil_path: path })

    // 3) Refresca user y mensajes
    props.user.foto_perfil = res.data.user.foto_perfil // URL pública
    photoSuccess.value = 'Foto actualizada.'
  } catch (err) {
    console.error(err)
    photoError.value = err?.response?.data?.message || 'No se pudo subir/actualizar la foto.'
  } finally {
    isUploading.value = false
    // Limpia el input file para permitir re-seleccionar el mismo archivo si se desea
    if (e.target) e.target.value = ''
  }
}

async function removePhoto() {
  photoError.value = ''
  photoSuccess.value = ''

  try {
    isUploading.value = true
    const res = await axios.put('/perfil', { remove_photo: true })
    props.user.foto_perfil = res.data.user.foto_perfil // será null
    photoPreview.value = null
    photoSuccess.value = 'Foto eliminada.'
  } catch (err) {
    console.error(err)
    photoError.value = err?.response?.data?.message || 'No se pudo eliminar la foto.'
  } finally {
    isUploading.value = false
  }
}

// --- Contraseña ---
const passwordForm = reactive({
  password: '',
  password_confirmation: ''
})
const passErrors = reactive({
  password: '',
  password_confirmation: ''
})
const passSuccess = ref('')
const passError = ref('')
const isChangingPass = ref(false)
const showPass = ref(false)

function validatePassword() {
  passErrors.password = ''
  passErrors.password_confirmation = ''
  passSuccess.value = ''
  passError.value = ''

  const pwd = passwordForm.password
  const cfm = passwordForm.password_confirmation

  if (!pwd || pwd.length < 8) {
    passErrors.password = 'La contraseña debe tener al menos 8 caracteres.'
  }
  if (cfm !== pwd) {
    passErrors.password_confirmation = 'La confirmación no coincide.'
  }
  return !passErrors.password && !passErrors.password_confirmation
}

async function submitPassword() {
  if (!validatePassword()) return

  isChangingPass.value = true
  passSuccess.value = ''
  passError.value = ''

  try {
    await axios.put('/perfil', {
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation
    })

    passSuccess.value = 'Contraseña actualizada.'
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (e) {
    passError.value = e?.response?.data?.message || 'No se pudo actualizar la contraseña.'
  } finally {
    isChangingPass.value = false
  }
}
</script>

<style scoped>
.card {
  border-radius: 12px;
  background-color: rgba(248, 249, 250, 0.8) !important;
  backdrop-filter: blur(8px);
}

/* Foto + overlay */
.profile-photo-wrapper {
  width: 112px;
  height: 112px;
}
.profile-photo-wrapper img {
  display: block;
}
.overlay-btn {
  position: absolute;
  inset: 0;
  border: 0;
  background: rgba(0, 40, 75, 0.0);
  color: #fff;
  opacity: 0;
  transition: background 0.2s ease, opacity 0.2s ease;
  border-radius: 50%;
}
.overlay-btn.show-overlay,
.profile-photo-wrapper:hover .overlay-btn {
  background: rgba(0, 40, 75, 0.72); /* azul #00284B opaco */
  opacity: 1;
  cursor: pointer;
}
.object-fit-cover {
  object-fit: cover;
}
</style>
