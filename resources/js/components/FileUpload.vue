<template>
  <div class="uploader">
    <h4>Cargar comprobante</h4>
    <!-- Dropzone clickeable -->
    <div
      class="upload-dropzone"
      :class="{ 'is-dragover': isDragOver, 'has-file': !!selectedFile }"
      role="button"
      tabindex="0"
      @click="openFileDialog"
      @keydown.enter.prevent="openFileDialog"
      @keydown.space.prevent="openFileDialog"
      @dragover.prevent="onDragOver"
      @dragleave.prevent="onDragLeave"
      @drop.prevent="onDrop"
    >
      <input
        ref="fileInput"
        class="d-none"
        type="file"
        :accept="accept"
        @change="onFileChange"
      />

      <!-- Contenido central -->
      <div class="dropzone-content d-flex flex-column justify-content-center align-items-center">
        <p class="upload-icon">
          <i class="fa-solid fa-upload"></i>
        </p>
        <p class="mb-1">
          Arrastra y suelta tu archivo aquí o
          <button class="btn btn-link p-0 align-baseline" @click.stop="openFileDialog">
            Selecciona un archivo
          </button>
        </p>
        <small v-if="hint" class="text-muted d-block mt-2">{{ hint }}</small>
      </div>
    </div>
    <div class="d-flex justify-content-between" style="color: #A9A9A9;">
      <p>Formatos aceptados: JPG, JPEG, PNG</p>
      <p>Tamaño máximo: 1MB</p>
    </div>

    <div v-if="has_template">
      <FileTemplate
        template_url="/templates/plantilla-secciones.xlsx"
        icon="/icons/excel-icon.png"
        file_title="Plantilla de Secciones"
        class="mt-3"
      />
    </div>

    <!-- Archivo cargado -->
    <div v-if="selectedFile" class="uploaded-file mt-2">
      <template v-if="!fileBusy">
        <div class="d-flex justify-content-between align-items-start">
          <div class="d-flex gap-2 align-items-center">
            <div class="file-icon d-flex justify-content-center align-items-center">
              <i class="fa-solid fa-image fs-5"></i>
            </div>
            <div>
              <p class="m-0 p-0">{{ selectedFile.name }}</p>
              <p class="m-0 p-0" style="color: #A9A9A9;">{{ prettySize(selectedFile.size) }}</p>
            </div>
          </div>
          <Filtros
            v-if="!uploadingTemp"
            v-model="meses"
            :options="mesesOptions"
            placeholder="Meses a pagar"
            direction="up"
          />
          <Filtros
            v-if="!uploadingTemp"
            v-model="tipoPagoId"
            :options="tiposPagoOptions"
            placeholder="Tipo de pago"
            direction="up"
          />
          <div class="amount-wrapper" v-if="!uploadingTemp">
            <input
              class="amount-input"
              type="number"
              inputmode="decimal"
              step="0.01"
              min="0"
              v-model="monto"
              placeholder="Monto pagado"
            />
            <i class="fa-solid fa-coins amount-icon"></i>
          </div>
          <button class="x-button" @click.stop="clearFile">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
  
        <!-- Progreso -->
        <div v-if="progress > 0 && uploadingTemp" class="mt-3">
          <div class="progress" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar" :style="{ width: progress + '%' }">
              {{ progress }}%
            </div>
          </div>
        </div>
      </template>
      <template v-else>
        <div class="d-flex justify-content-center align-items-center flex-grow-1">
          <i class="fa-solid fa-spinner fa-spin-pulse"></i>
        </div>
      </template>
    </div>

    <!-- Mensajes -->
    <p v-if="message" class="mt-2 text-center">{{ message }}</p>

    <!-- Botón enviar -->
    <div class="d-flex justify-content-center mt-3">
      <button
        v-if="selectedFile && !uploadingTemp && !fileBusy"
        class="btn btn-primary px-4"
        @click="uploadFile"
        :disabled="!selectedFile || uploading"
      >
        <i class="fa-solid fa-paper-plane"></i>
        <span class="ms-2" v-if="!uploading">Enviar</span>
        <span class="ms-2" v-else>Enviando…</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import Filtros from './Filtros.vue'
import FileTemplate from './FileTemplate.vue'

// Props
const props = defineProps({
  accept: { type: String, default: '' },
  hint:   { type: String, default: '' },
  tiposPago: { type: Array, default: () => [] }
})

const tiposPagoOptions = computed(() =>
  (props.tiposPago || []).map(t => ({ id: t.id, nombre: t.nombre }))
)

// Estado
const fileInput     = ref(null)
const selectedFile  = ref(null)
const uploadedPath  = ref(null) // <- ruta en storage para poder borrar
const message       = ref('')
const progress      = ref(0)
const isDragOver    = ref(false)
const dragCounter   = ref(0)
const uploadingTemp = ref(false) // upload temporal (pre-enviar)
const uploading     = ref(false)
const fileBusy      = ref(false)
const cleanupDone = ref(false)

const tipoPagoId = ref(null)
const monto      = ref('')

const meses = ref(null)
const mesesOptions = Array.from({ length: 12 }, (_, i) => {
  const n = i + 1
  return { id: n, nombre: `${n} mes${n > 1 ? 'es' : ''}` }
})

// === Validación dura (1MB, JPG/PNG) ===
const MAX_SIZE_BYTES = 1 * 1024 * 1024;
const ALLOWED_MIME = new Set(['image/jpeg', 'image/png']);
const ALLOWED_EXT  = ['.jpg', '.jpeg', '.png'];

function isAllowedType(file) {
  if (ALLOWED_MIME.has(file.type)) return true;
  const name = (file.name || '').toLowerCase();
  return ALLOWED_EXT.some(ext => name.endsWith(ext));
}

function validateFile(file) {
  if (!file) return false;
  if (!isAllowedType(file)) {
    message.value = 'Formato inválido. Solo se permiten JPG, JPEG o PNG.';
    return false;
  }
  if (file.size > MAX_SIZE_BYTES) {
    message.value = 'El archivo supera el máximo de 1 MB.';
    return false;
  }
  return true;
}

// Helpers UI
function prettySize(bytes) {
  if (!bytes && bytes !== 0) return ''
  const units = ['B','KB','MB','GB','TB']
  let i = 0, size = bytes
  while (size >= 1024 && i < units.length - 1) { size /= 1024; i++ }
  return `${size.toFixed(i ? 1 : 0)} ${units[i]}`
}

const openFileDialog = () => fileInput.value?.click()

// === Ciclo: borrar temp anterior -> subir nuevo temp ===
async function deleteUploadedIfAny() {
  if (!uploadedPath.value) return
  fileBusy.value = true
  message.value = ''

  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    await axios.delete('/upload', {
      data: { path: uploadedPath.value },
      headers: { ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}) }
    })
  } catch (e) {
    console.warn('No se pudo eliminar el archivo previo:', e)
  } finally {
    uploadedPath.value = null
    fileBusy.value = false
  }
}

function sendCleanupBeacon() {
  if (!uploadedPath.value || cleanupDone.value) return
  cleanupDone.value = true

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

  // Usamos sendBeacon (POST) -> requiere endpoint POST
  const data = new FormData()
  data.append('path', uploadedPath.value)
  if (csrf) data.append('_token', csrf)

  // Si no existe sendBeacon, hacemos fetch keepalive
  if (navigator.sendBeacon) {
    navigator.sendBeacon('/upload/cleanup', data)
  } else {
    fetch('/upload/cleanup', { method: 'POST', body: data, keepalive: true })
      .catch(() => {}) // no bloqueamos la salida
  }
}

function handlePageHide() {
  // pagehide/beforeunload: no hay tiempo para axios; usa beacon
  sendCleanupBeacon()
}

onMounted(() => {
  window.addEventListener('pagehide', handlePageHide)      // navegación SPA o cierre
  window.addEventListener('beforeunload', handlePageHide)  // fallback adicional
})

onBeforeUnmount(() => {
  // 1) Quita listeners
  window.removeEventListener('pagehide', handlePageHide)
  window.removeEventListener('beforeunload', handlePageHide)

  // 2) Limpia en desmontaje normal de Vue (cambio de ruta dentro de tu SPA)
  //    Aquí sí podemos intentar el DELETE normal porque aún estamos "vivos".
  if (uploadedPath.value && !cleanupDone.value) {
    cleanupDone.value = true
    // Llama a tu método actual (no esperes la promesa)
    deleteUploadedIfAny()
  }
})

async function beginTempUpload(file) {
  // sube a /upload y guarda path para poder borrarlo
  const formData = new FormData()
  formData.append('file', file)
  // opcional: carpeta custom -> formData.append('path', 'uploads/comprobantes/tmp')

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

  uploadingTemp.value = true
  message.value = ''
  progress.value = 0

  try {
    const res = await axios.post('/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
      },
      onUploadProgress: (evt) => {
        const total =
          evt.total ||
          (evt.target && evt.target.getResponseHeader && Number(evt.target.getResponseHeader('Content-Length'))) ||
          0
        if (total > 0) {
          progress.value = Math.round((evt.loaded / total) * 100)
        } else {
          const approx = Math.min(95, Math.floor((evt.loaded / (1024 * 1024)) * 10))
          progress.value = approx
        }
      }
    })

    progress.value = 100
    uploadedPath.value = res?.data?.path || null
    message.value = res?.data?.message || 'Archivo subido correctamente'
  } catch (err) {
    console.error(err)
    message.value = 'Error al subir el archivo'
    // si falló el upload, limpia selección
    selectedFile.value = null
    if (fileInput.value) fileInput.value.value = ''
  } finally {
    uploadingTemp.value = false
  }
}

// Handlers
const onFileChange = async (event) => {
  const file = event.target.files?.[0]
  if (!validateFile(file)) return
  // Si hay uno previo ya subido, bórralo primero
  await deleteUploadedIfAny()
  selectedFile.value = file
  await beginTempUpload(file)
}

const onDragOver = () => {
  isDragOver.value = true
  dragCounter.value++
}

const onDragLeave = () => {
  dragCounter.value = Math.max(0, dragCounter.value - 1)
  if (dragCounter.value === 0) isDragOver.value = false
}

const onDrop = async (e) => {
  dragCounter.value = 0
  isDragOver.value = false
  const file = e.dataTransfer?.files?.[0]
  if (!validateFile(file)) return
  await deleteUploadedIfAny()
  selectedFile.value = file
  await beginTempUpload(file)
}

const clearFile = async () => {
  // al presionar la X: borrar en servidor si ya estaba subido
  await deleteUploadedIfAny()
  selectedFile.value = null
  progress.value = 0
  message.value = ''
  tipoPagoId.value = null
  monto.value = ''

  if (fileInput.value) fileInput.value.value = ''
  meses.value = null
}

// Botón "Enviar" (a otra ruta). Aún no implementado en backend.
const uploadFile = async () => {
  if (!uploadedPath.value) {
    message.value = 'Primero sube un archivo válido.'
    return
  }
  if (!tipoPagoId.value) {
    message.value = 'Selecciona el tipo de pago.'
    return
  }
  const montoNum = Number(monto.value)
  if (!monto.value || Number.isNaN(montoNum) || montoNum <= 0) {
    message.value = 'Ingresa un monto válido mayor a 0.'
    return
  }

  uploading.value = true
  message.value = ''

  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

    const payload = {
      path: uploadedPath.value,
      meses_pagados: meses.value?.id ?? meses.value ?? 1,
      tipo_pago_id: tipoPagoId.value,
      monto_pagado: montoNum,
    }

    const { data } = await axios.post('/pagos', payload, {
      headers: { ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}) },
    })

    message.value = data?.message ?? 'Pago registrado.'

    // Limpieza visual
    selectedFile.value = null
    progress.value = 0
    message.value = ''
    tipoPagoId.value = null
    monto.value = ''
  } catch (err) {
    console.error(err)
    message.value = err?.response?.data?.message ?? 'No se pudo registrar el pago.'
  } finally {
    uploading.value = false
  }
}

</script>

<style scoped>
.uploader {
  margin: 1rem 6rem;
}
.upload-dropzone {
  padding: 24px;
  border: 2px dashed #A9A9A9;
  border-radius: 8px;
  background: rgba(169, 169, 169, 0.05);
  cursor: pointer;
  transition: all .15s ease-in-out;
  outline: none;
}
.upload-dropzone:hover,
.upload-dropzone:focus {
  background: rgba(131, 170, 107, 0.08);
  border-color: #83AA6B;
}
.upload-dropzone.is-dragover {
  background: rgba(131, 170, 107, 0.08);
  border-color: #83AA6B;
  box-shadow: 0 0 0 3px rgba(75, 141, 255, 0.15) inset;
}
.upload-dropzone.has-file {
  background: rgba(131, 170, 107, 0.08);
  border-color: #83AA6B;
}
.upload-icon {
  color: #A9A9A9;
  font-size: 72px;
}
.uploaded-file {
  background: rgba(217, 217, 217, 0.3);
  border-radius: 1rem;
  padding: 1rem;
}
.dropzone-content {
  min-height: 300px;
  place-items: center;
}
.file-icon {
  background: white;
  padding: 0.75rem;
  border: 1px solid #A9A9A9;
  border-radius: 0.5rem;
  color: #A9A9A9;
}
.progress {
  height: 14px;
}
.progress-bar {
  font-size: 12px;
  line-height: 14px;
}
.x-button {
  all: unset;
  cursor: pointer;
  display: inline;  
}

/* === Monto con estilo de search bar === */
.amount-wrapper {
  position: relative;
  width: 280px;            /* puedes ajustar o usar 100% si prefieres */
  margin-left: .5rem;      /* pequeño espacio del selector */
}

.amount-input {
  width: 100%;
  padding: .3rem 2.75rem .3rem 1rem; /* deja espacio al ícono */
  border: 1px solid #B5B5B5;
  border-radius: 0.8rem;
  font-size: 1rem;
  color: #777;
  outline: none;
  appearance: textfield;           /* quita flechas en algunos navegadores */
}

/* Quitar spinners en number (Chrome/Edge) */
.amount-input::-webkit-outer-spin-button,
.amount-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.amount-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #B5B5B5;
  font-size: 1rem;
}

</style>
