<template>
  <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div
      class="bg-white p-5 rounded shadow text-center"
      style="max-width: 800px; width: 100%;"
    >
      <img
        src="/images/logo_.jpg"
        alt="Logo"
        class="mb-4"
        style="width: 180px;"
      />

      <h2 class="mb-3">Sección de Inventario</h2>
      <p class="text-muted fs-5">Registrar Movimiento de Inventario</p>

      <form @submit.prevent="guardarMovimiento" class="row g-3 needs-validation mt-4" novalidate ref="inventoryForm">
        <!-- Nombre del producto -->
        <div class="col-12">
          <label for="producto" class="form-label fs-5">Nombre del Producto</label>
          <select v-model="form.producto" id="producto" class="form-select form-select-lg" required>
            <option disabled value="">Selecciona un producto</option>
            <option value="uniformes">Uniformes</option>
            <option value="utiles">Útiles</option>
            <option value="otros">Otros</option>
          </select>
          <div class="invalid-feedback">
            Por favor selecciona un producto.
          </div>
        </div>

        <!-- Campos adicionales si es "uniformes" -->
        <div v-if="form.producto === 'uniformes'" class="row">
          <div class="col-md-6">
            <label for="tipoUniforme" class="form-label fs-5">Tipo de Uniforme</label>
            <select v-model="form.tipoUniforme" id="tipoUniforme" class="form-select form-select-lg" required>
              <option disabled value="">Selecciona un tipo</option>
              <option value="chumpa">Chumpa</option>
              <option value="playera">Playera</option>
              <option value="pantalon">Pantalón</option>
              <option value="falda">Falda</option>
            </select>
            <div class="invalid-feedback">
              Por favor selecciona un tipo de uniforme.
            </div>
          </div>
          <div class="col-md-6">
            <label for="talla" class="form-label fs-5">Talla</label>
            <input
              type="text"
              id="talla"
              v-model="form.talla"
              class="form-control form-control-lg"
              placeholder="Ej. S, M, L, 10, 12, etc."
              required
            />
            <div class="invalid-feedback">
              Por favor ingresa la talla.
            </div>
          </div>
        </div>

        <!-- Campo adicional si es "otros" -->
        <div class="col-12" v-if="form.producto === 'otros'">
          <label for="productoPersonalizado" class="form-label fs-5">Especifica el producto</label>
          <input
            type="text"
            id="productoPersonalizado"
            v-model="form.productoPersonalizado"
            class="form-control form-control-lg"
            placeholder="Ej. Marcadores permanentes"
            required
          />
          <div class="invalid-feedback">
            Por favor especifica el producto.
          </div>
        </div>

        <!-- Cantidad -->
        <div class="col-md-6">
          <label for="cantidad" class="form-label fs-5">Cantidad a Ingresar</label>
          <input
            type="number"
            id="cantidad"
            v-model="form.cantidad"
            class="form-control form-control-lg"
            min="1"
            required
          />
          <div class="invalid-feedback">
            Por favor ingresa una cantidad válida (mínimo 1).
          </div>
        </div>

        <!-- Fecha -->
        <div class="col-md-6">
          <label for="fecha" class="form-label fs-5">Fecha</label>
          <input
            type="date"
            id="fecha"
            v-model="form.fecha"
            class="form-control form-control-lg"
            required
          />
          <div class="invalid-feedback">
            Por favor selecciona una fecha.
          </div>
        </div>

        <!-- Botón -->
        <div class="col-12">
          <button type="submit" class="btn btn-success btn-lg w-100">
            Guardar Movimiento
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      form: {
        producto: '',
        productoPersonalizado: '',
        tipoUniforme: '',
        talla: '',
        cantidad: '',
        fecha: ''
      }
    };
  },
  methods: {
    async guardarMovimiento() {
      const form = this.$refs.inventoryForm;
      
      // Verificar la validez del formulario
      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        
        // Mostrar alerta de error
        await Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "¡Por favor completa todos los campos correctamente!",
        });
        return;
      }

      let nombreFinalProducto = '';

      if (this.form.producto === 'uniformes') {
        nombreFinalProducto = `Uniforme - ${this.form.tipoUniforme} - Talla ${this.form.talla}`;
      } else if (this.form.producto === 'otros') {
        nombreFinalProducto = this.form.productoPersonalizado;
      } else {
        nombreFinalProducto = this.form.producto;
      }

      console.log("Movimiento registrado:", {
        producto: nombreFinalProducto,
        cantidad: this.form.cantidad,
        fecha: this.form.fecha
      });

      // Mostrar alerta de éxito
      await Swal.fire({
        title: "Guardado Correctamente!",
        text: `Movimiento registrado para "${nombreFinalProducto}"`,
        icon: "success",
        draggable: true
      });

      // Resetear formulario
      this.form = {
        producto: '',
        productoPersonalizado: '',
        tipoUniforme: '',
        talla: '',
        cantidad: '',
        fecha: ''
      };

      // Resetear validación visual
      form.classList.remove('was-validated');
    }
  }
};
</script>

<style>
body {
  font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, sans-serif; 
}
</style>