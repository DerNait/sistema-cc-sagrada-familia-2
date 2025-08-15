<template>
  <div class="upload-container">
    <input type="file" @change="onFileChange" />
    <button @click="uploadFile" :disabled="!selectedFile">Subir</button>

    <p v-if="message">{{ message }}</p>
    <p v-if="progress > 0">Progreso: {{ progress }}%</p>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      selectedFile: null,
      message: '',
      progress: 0
    }
  },
  methods: {
    onFileChange(event) {
      this.selectedFile = event.target.files[0]
    },
    async uploadFile() {
      try {
        const formData = new FormData()
        formData.append('file', this.selectedFile)

        const csrfToken = document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute('content')

        const res = await axios.post('/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': csrfToken
          },
          onUploadProgress: progressEvent => {
            this.progress = Math.round(
              (progressEvent.loaded / progressEvent.total) * 100
            )
          }
        })

        this.message = res.data.message
      } catch (error) {
        console.error(error)
        this.message = 'Error al subir el archivo'
      }
    }
  }
}
</script>

<style scoped>
.upload-container {
  padding: 10px;
  border: 2px dashed #ccc;
  border-radius: 5px;
  width: fit-content;
}
button {
  margin-top: 10px;
  padding: 6px 12px;
  cursor: pointer;
}
</style>
