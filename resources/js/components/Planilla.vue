<template>
    <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
      <div class="bg-white p-5 rounded shadow text-center" style="max-width: 600px; width: 100%;">
        <img
          src="/images/logo_.jpg"
          alt="Logo"
          class="mb-4"
          style="width: 150px;"
        />
  
        <h2 class="mb-3">Descargar Planilla</h2>
        <p class="text-muted">Haz clic en el botón para descargar la planilla de empleados en formato Excel.</p>
  
        <!-- Botón para descargar el archivo Excel -->
        <button
          class="btn btn-primary mt-4"
          @click="exportarEmpleados"
          :disabled="isExporting"
        >
          Descargar Planilla en Excel
        </button>
  
        <!-- Mensaje de carga -->
        <div v-if="isExporting" class="mt-3">
          <p class="text-muted">Generando archivo... Por favor espera.</p>
        </div>
  
        <!-- Mensaje de error -->
        <div v-if="errorMessage" class="mt-3">
          <p class="text-danger">{{ errorMessage }}</p>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        isExporting: false,  // Estado de carga
        errorMessage: "",  // Mensaje de error
      };
    },
    methods: {
      // Método para descargar el archivo Excel
      exportarEmpleados() {
        this.isExporting = true; // Cambiar estado a 'cargando'
        this.errorMessage = ""; // Resetear mensaje de error
  
        // Realiza una solicitud GET a la ruta de exportación
        fetch('/exportar-empleados')
          .then(response => {
            if (!response.ok) {
              throw new Error('No se pudo generar el archivo');
            }
            window.location.href = '/exportar-empleados'; // Descargar el archivo
          })
          .catch(error => {
            this.errorMessage = error.message;  // Mostrar error
          })
          .finally(() => {
            this.isExporting = false; // Volver al estado normal
          });
      }
    }
  };
  </script>
  
  <style scoped>
  /* Puedes agregar estilo adicional aquí si lo deseas */
  </style>
  