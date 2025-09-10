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

    <!-- Archivo cargado -->
    <div v-if="selectedFile" class="uploaded-file mt-2">
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
        <button class="x-button" @click.stop="clearFile">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <!-- Progreso -->
      <div v-if="progress > 0" class="mt-3">
        <div class="progress" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-bar" :style="{ width: progress + '%' }">
            {{ progress }}%
          </div>
        </div>
      </div>
    </div>

    <!-- Botón enviar -->
    <div class="d-flex justify-content-center mt-3">
      <button
        v-if="selectedFile"
        class="btn btn-primary px-4"
        @click="uploadFile"
        :disabled="!selectedFile || uploading"
      >
        <i class="fa-solid fa-paper-plane"></i>
        <span class="ms-2" v-if="!uploading">Enviar</span>
        <span class="ms-2" v-else>Enviando…</span>
      </button>
    </div>

    <!-- Mensajes -->
    <p v-if="message" class="mt-2 text-center">{{ message }}</p>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'DragDropUploader',
  props: {
    accept: { type: String, default: '' }, // e.g. "image/*,.pdf"
    hint:   { type: String, default: '' }  // e.g. "PDF o imágenes, máximo 10MB"
  },
  data() {
    return {
      selectedFile: null,
      message: '',
      progress: 0,
      isDragOver: false,
      dragCounter: 0,
      uploading: false
    }
  },
  methods: {
    openFileDialog() {
      this.$refs.fileInput?.click()
    },
    onFileChange(event) {
      const file = event.target.files?.[0]
      if (file) {
        this.selectedFile = file
        this.message = ''
        this.progress = 0
      }
    },
    onDragOver() {
      this.isDragOver = true
      this.dragCounter++
    },
    onDragLeave() {
      this.dragCounter = Math.max(0, this.dragCounter - 1)
      if (this.dragCounter === 0) this.isDragOver = false
    },
    onDrop(e) {
      this.dragCounter = 0
      this.isDragOver = false
      const file = e.dataTransfer?.files?.[0]
      if (file) {
        // opcional: valida por tipo/tamaño aquí
        this.selectedFile = file
        this.message = ''
        this.progress = 0
      }
    },
    prettySize(bytes) {
      if (!bytes && bytes !== 0) return ''
      const units = ['B','KB','MB','GB','TB']
      let i = 0
      let size = bytes
      while (size >= 1024 && i < units.length - 1) {
        size /= 1024
        i++
      }
      return `${size.toFixed(i ? 1 : 0)} ${units[i]}`
    },
    clearFile() {
      this.selectedFile = null
      this.progress = 0
      this.message = ''
      if (this.$refs.fileInput) this.$refs.fileInput.value = ''
    },
    async uploadFile() {
      if (!this.selectedFile) return
      try {
        this.uploading = true
        this.message = ''
        const formData = new FormData()
        formData.append('file', this.selectedFile)

        const csrfToken = document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute('content')

        const res = await axios.post('/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
          },
          onUploadProgress: (evt) => {
            // total puede venir undefined según el server; manejemos fallback
            const total = evt.total || (evt.target && evt.target.getResponseHeader && Number(evt.target.getResponseHeader('Content-Length'))) || 0
            if (total > 0) {
              this.progress = Math.round((evt.loaded / total) * 100)
            } else {
              // fallback: si no hay total, simula progreso básico
              const approx = Math.min(95, Math.floor((evt.loaded / (1024 * 1024)) * 10)) // arbitrario
              this.progress = approx
            }
          }
        })

        this.progress = 100
        this.message = res?.data?.message || 'Archivo subido correctamente'
      } catch (error) {
        console.error(error)
        this.message = 'Error al subir el archivo'
      } finally {
        this.uploading = false
      }
    }
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
</style>
