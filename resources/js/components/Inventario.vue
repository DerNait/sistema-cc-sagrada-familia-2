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

      <form @submit.prevent="guardarMovimiento" class="text-start mt-4">
        <!-- Nombre del producto -->
        <div class="mb-3">
          <label for="producto" class="form-label fs-5">Nombre del Producto</label>
          <select v-model="form.producto" id="producto" class="form-select form-select-lg" required>
            <option disabled value="">Selecciona un producto</option>
            <option value="uniformes">Uniformes</option>
            <option value="utiles">Útiles</option>
            <option value="otros">Otros</option>
          </select>
        </div>

        <!-- Campos adicionales si es "uniformes" -->
        <div v-if="form.producto === 'uniformes'">
          <div class="mb-3">
            <label for="tipoUniforme" class="form-label fs-5">Tipo de Uniforme</label>
            <select v-model="form.tipoUniforme" id="tipoUniforme" class="form-select form-select-lg" required>
              <option disabled value="">Selecciona un tipo</option>
              <option value="chumpa">Chumpa</option>
              <option value="playera">Playera</option>
              <option value="pantalon">Pantalón</option>
              <option value="falda">Falda</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="talla" class="form-label fs-5">Talla</label>
            <input
              type="text"
              id="talla"
              v-model="form.talla"
              class="form-control form-control-lg"
              placeholder="Ej. S, M, L, 10, 12, etc."
              required
            />
          </div>
        </div>

        <!-- Campo adicional si es "otros" -->
        <div class="mb-3" v-if="form.producto === 'otros'">
          <label for="productoPersonalizado" class="form-label fs-5">Especifica el producto</label>
          <input
            type="text"
            id="productoPersonalizado"
            v-model="form.productoPersonalizado"
            class="form-control form-control-lg"
            placeholder="Ej. Marcadores permanentes"
            required
          />
        </div>

        <!-- Cantidad -->
        <div class="mb-3">
          <label for="cantidad" class="form-label fs-5">Cantidad a Ingresar</label>
          <input
            type="number"
            id="cantidad"
            v-model="form.cantidad"
            class="form-control form-control-lg"
            min="1"
            required
          />
        </div>

        <!-- Fecha -->
        <div class="mb-4">
          <label for="fecha" class="form-label fs-5">Fecha</label>
          <input
            type="date"
            id="fecha"
            v-model="form.fecha"
            class="form-control form-control-lg"
            required
          />
        </div>

        <!-- Botón -->
        <button type="submit" class="btn btn-success btn-lg w-100">
          Guardar Movimiento
        </button>
      </form>

      <div v-if="mensaje" class="alert alert-success mt-4">
        {{ mensaje }}
      </div>
    </div>
  </div>
</template>
