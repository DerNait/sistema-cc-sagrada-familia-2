<template>
  <div class="container my-4">
    <h3 class="text-center text-success mb-4">Saldos</h3>

    <!-- Vencidos -->
    <h5>Vencidos</h5>
    <table class="table table-bordered text-center">
      <thead>
        <tr>
          <th>Concepto</th>
          <th>Vence</th>
          <th>Detalle de cuenta corriente</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Deudores por cuotas</td>
          <td>17/05/2025</td>
          <td>Q.4,000.00</td>
        </tr>
        <tr class="table-danger">
          <td><strong>Total saldo vencido</strong></td>
          <td></td>
          <td><strong>Q.4,000.00</strong></td>
        </tr>
        <tr class="table-success">
          <td><strong>Monto a pagar</strong></td>
          <td></td>
          <td><strong>Q.4,000.00</strong></td>
        </tr>
      </tbody>
    </table>

    <!-- Por vencer -->
    <h5>Por vencer</h5>
    <table class="table table-bordered text-center">
      <thead>
        <tr>
          <th>Concepto</th>
          <th>Vence</th>
          <th>Detalle de cuenta corriente</th>
        </tr>
      </thead>
      <tbody>
        <tr class="table-success">
          <td><strong>Monto a pagar</strong></td>
          <td></td>
          <td><strong>Q0.00</strong></td>
        </tr>
      </tbody>
    </table>

    <!-- Total operación -->
    <h4 class="text-center text-danger my-4">
      Total de la operación: Q4,000.00
    </h4>

    <!-- Subir comprobante -->
    <div class="mb-3">
      <label for="fileInput" class="form-label">Subir comprobante de pago</label>
      <div class="input-group">
        <input type="file" class="form-control" id="fileInput" @change="handleFileUpload">
      </div>
    </div>

    <div class="text-center">
      <button class="btn btn-success" @click="submitForm">Enviar</button>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    handleFileUpload(event) {
      const file = event.target.files[0];
      console.log("Archivo seleccionado:", file);
    },
    submitForm() {
      alert("Comprobante enviado");
    }
  }
};
</script>

<style scoped>
table {
  font-size: 0.95rem;
}
</style>
