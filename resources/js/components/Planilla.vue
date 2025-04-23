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
      
    
      <button
        class="btn btn-primary mt-4"
        @click="exportarEmpleados"
        :disabled="isExporting"
      >
        <span v-if="!isExporting">Descargar Planilla en Excel</span>
        <span v-else>Generando archivo...</span>
      </button>
      
   
      <div v-if="isExporting" class="mt-3">
        <p class="text-muted">Procesando... Por favor espera.</p>
      </div>
      
    
      <div v-if="errorMessage" class="mt-3 alert alert-danger">
        {{ errorMessage }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isExporting: false,  
      errorMessage: "",    
    };
  },
  methods: {
    
    exportarEmpleados() {
      this.isExporting = true; 
      this.errorMessage = "";
     
      const link = document.createElement('a');
      link.href = '/exportar-empleados';
      link.setAttribute('download', 'empleados.xlsx'); 
      document.body.appendChild(link);
      
      
      setTimeout(() => {
        link.click();
        document.body.removeChild(link);
       
        setTimeout(() => {
          this.isExporting = false;
        }, 1500);
      }, 500);
    }
  }
};
</script>