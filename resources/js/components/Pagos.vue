<template>
  <div class="container-fluid min-vh-100 bg-light py-5 px-3">
    <div class="bg-white rounded shadow p-5 mx-auto" style="max-width: 900px;">
      <div class="text-center mb-4">
        <img
          src="/images/logo_.jpg"
          alt="Logo"
          class="mb-3"
          style="width: 150px;"
        />
        <h2>Sección de Pagos</h2>
      </div>

      <!-- Información del estudiante -->
      <div class="mb-4">
        <h4 class="mb-3">Estudiante: <strong>Juan Pérez</strong></h4>
        <p><strong>Concepto:</strong> Deudores por cuotas</p>
        <p><strong>Vence:</strong> 25/05/2025</p>
        <p><strong>Detalle de cuenta corriente:</strong> Q.1500</p>
        <h5 class="mt-4 text-danger">
          Total saldo vencido: <strong>Q.1500</strong>
        </h5>
      </div>

      <!-- Subida de comprobante -->
      <div class="mb-4">
        <label for="comprobante" class="form-label fw-bold">
          📎 Subir comprobante de pago:
        </label>
        <input
          class="form-control"
          type="file"
          id="comprobante"
          @change="handleFileUpload"
        />
      </div>

      <!-- Botón de enviar -->
      <div class="d-grid">
        <button class="btn btn-primary btn-lg" @click="enviarPago">
          Enviar para confirmar
        </button>
      </div>

      <!-- Mensaje de confirmación -->
      <div v-if="confirmado" class="alert alert-success mt-4 text-center" role="alert">
        ¡Comprobante enviado para confirmar!
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      comprobante: null,
      confirmado: false,
    };
  },
  methods: {
    handleFileUpload(event) {
      this.comprobante = event.target.files[0];
    },
    enviarPago() {
      if (this.comprobante) {
        this.confirmado = true;
      } else {
        alert("Por favor, sube un comprobante antes de enviar.");
      }
    },
  },
};
</script>

<style scoped>
input[type="file"] {
  padding: 10px;
}
</style>
