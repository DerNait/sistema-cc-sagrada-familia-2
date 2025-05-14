<template>
  <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div
      class="bg-white p-5 rounded shadow"
      style="max-width: 1000px; width: 100%;"
    >
      <img
        src="/images/logo_.jpg"
        alt="Logo"
        class="mb-4 d-block mx-auto"
        style="width: 180px;"
      />

      <h2 class="mb-3 text-center">Sección de Inventario</h2>
      <p class="text-muted fs-5 text-center">Filtrar Productos</p>

      <!-- Formulario de Filtrado -->
      <div class="filter-section mb-4 p-4 border rounded">
        <h4 class="mb-3">Filtros de Búsqueda</h4>
        
        <div class="row g-3">
          <!-- Filtro por ID -->
          <div class="col-md-4">
            <label for="filter-id" class="form-label">ID del Producto</label>
            <input
              type="number"
              id="filter-id"
              v-model.number="filters.id"
              class="form-control"
              placeholder="Buscar por ID"
              min="0"
              step="1"
            >
          </div>

          <!-- Filtro por Nombre -->
          <div class="col-md-4">
            <label for="filter-nombre" class="form-label">Nombre del Producto</label>
            <input
              type="text"
              id="filter-nombre"
              v-model="filters.nombre"
              class="form-control"
              placeholder="Buscar por nombre"
            >
          </div>

          <!-- Filtro por Tipo -->
          <div class="col-md-4">
            <label for="filter-tipo" class="form-label">Tipo de Producto</label>
            <select
              id="filter-tipo"
              v-model="filters.tipo"
              class="form-select"
            >
              <option value="">Todos los tipos</option>
              <option value="uniformes">Uniformes</option>
              <option value="utiles">Útiles</option>
              <option value="otros">Otros</option>
            </select>
          </div>

          <!-- Filtro por Rango de Precio -->
          <div class="col-md-6">
            <label class="form-label">Rango de Precio</label>
            <div class="input-group">
              <input
                type="number"
                v-model.number="filters.precio_min"
                class="form-control"
                placeholder="Mínimo"
                min="0"
              >
              <span class="input-group-text">a</span>
              <input
                type="number"
                v-model.number="filters.precio_max"
                class="form-control"
                placeholder="Máximo"
                min="0"
              >
            </div>
          </div>

          <!-- Filtro por Fecha -->
          <div class="col-md-6">
            <label for="filter-fecha" class="form-label">Fecha de Ingreso</label>
            <input
              type="date"
              id="filter-fecha"
              v-model="filters.fecha_ingreso"
              class="form-control"
            >
          </div>

          <!-- Botones de acción -->
          <div class="col-12 d-flex justify-content-end gap-2">
            <button
              @click="resetFilters"
              class="btn btn-outline-secondary"
            >
              Limpiar Filtros
            </button>
            <button
              @click="aplicarFiltros"
              class="btn btn-primary"
            >
              Aplicar Filtros
            </button>
          </div>
        </div>
      </div>

      <!-- Resultados del Filtrado -->
      <div class="results-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4>Resultados</h4>
          <div class="text-muted">
            Mostrando {{ filteredProducts.length }} productos
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Precio Unitario</th>
                <th>Cantidad</th>
                <th>Fecha Ingreso</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in filteredProducts" :key="product.id">
                <td>{{ product.id }}</td>
                <td>{{ product.nombre }}</td>
                <td>{{ product.tipo }}</td>
                <td>${{ product.precio_unitario.toLocaleString() }}</td>
                <td>{{ product.cantidad }}</td>
                <td>{{ formatDate(product.fecha_ingreso) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      filters: {
        id: null,
        nombre: '',
        tipo: '',
        precio_min: null,
        precio_max: null,
        fecha_ingreso: ''
      },
      products: [] // Aquí se cargarán los productos desde Laravel
    };
  },
  created() {
    this.cargarProductos();
  },
  computed: {
    filteredProducts() {
      return this.products.filter(product => {
        return (
          (this.filters.id === null || product.id == this.filters.id) &&
          (this.filters.nombre === '' || 
            product.nombre.toLowerCase().includes(this.filters.nombre.toLowerCase())) &&
          (this.filters.tipo === '' || product.tipo === this.filters.tipo) &&
          (this.filters.precio_min === null || product.precio_unitario >= this.filters.precio_min) &&
          (this.filters.precio_max === null || product.precio_unitario <= this.filters.precio_max) &&
          (this.filters.fecha_ingreso === '' || product.fecha_ingreso === this.filters.fecha_ingreso)
        );
      });
    }
  },
  methods: {
    async cargarProductos() {
      try {
        // Aquí debes hacer la llamada a tu API Laravel
        const response = await axios.get('/api/productos');
        this.products = response.data;
      } catch (error) {
        console.error('Error al cargar productos:', error);
      }
    },
    aplicarFiltros() {
      // Los filtros se aplican automáticamente gracias a la propiedad computada
      console.log('Filtros aplicados:', this.filters);
    },
    resetFilters() {
      this.filters = {
        id: null,
        nombre: '',
        tipo: '',
        precio_min: null,
        precio_max: null,
        fecha_ingreso: ''
      };
    },
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      return new Date(dateString).toLocaleDateString('es-ES', options);
    }
  }
};
</script>

<style scoped>
.filter-section {
  background-color: #f8f9fa;
}

.table th {
  background-color: #343a40;
  color: white;
}

.table-responsive {
  max-height: 500px;
  overflow-y: auto;
}

.btn {
  min-width: 120px;
}
</style>