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
          <!-- Filtro por ID si se agruegan mas productos cambiar max -->
          <div class="col-md-4">
            <label for="filter-id" class="form-label">ID del Producto</label>
            <input
              type="number"
              id="filter-id"
              v-model.number="filters.id"
              class="form-control"
              placeholder="Buscar por ID"
              min="0"
              max="3"
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
              v-model="filters.tipo_producto_id"
              class="form-select"
            >
              <option :value="null">Todos los tipos</option>
              <option 
                v-for="tipo in tipos" 
                :key="tipo.id" 
                :value="tipo.id"
              >
                {{ tipo.nombre }}
              </option>
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
              class="btn btn-danger"
            >
              Limpiar Filtros
            </button>
            <button
              @click="aplicarFiltros"
              class="btn btn-success"
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
            Mostrando {{ productos.length }} productos
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
              <tr v-for="product in productos" :key="product.id">
                <td>{{ product.id }}</td>
                <td>{{ product.producto_nombre || product.nombre }}</td>
                <td>{{ product.tipo_nombre || product.tipo }}</td>
                <td>Q {{ product.precio_unitario.toLocaleString() }}</td>
                <td>{{ product.cantidad }}</td>
                <td>{{ formatDate(product.fecha_ingreso) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        </div>
          <h1 class="mb-3">Descargar Inventario</h1>
          <p class="text-muted">Haz clic en el botón para descargar la planilla de Inventario en formato Excel.</p>
          
        
          <button
            class="btn btn-success mt-4"
            @click="exportarEmpleados"
            :disabled="isExporting"
          >
            <span v-if="!isExporting">Descargar Inventario en Excel</span>
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
      filters: {
        id: null,
        nombre: '',
        tipo_producto_id: null,
        precio_min: null,
        precio_max: null,
        fecha_ingreso: ''
      },
      productos: [],
      tipos: [],
      loading: false,
      error: null,
      isExporting: false,  
      errorMessage: ""   
    }
  },
  created() {
    this.cargarDatosIniciales();
  },
  methods: {
    async cargarDatosIniciales() {
      try {
        this.loading = true;
        await Promise.all([
          this.cargarTipos(),
          this.cargarProductos()
        ]);
      } catch (error) {
        this.error = "Error al cargar datos iniciales";
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    async cargarTipos() {
      try {
        const { data } = await axios.get('/productos/tipos');
        this.tipos = data;
      } catch (error) {
        console.error('Error cargando tipos:', error);
        throw error;
      }
    },
    async cargarProductos() {
      try {
        // Limpiar filtros nulos
        const params = {
          id: this.filters.id || undefined,
          nombre: this.filters.nombre || undefined,
          tipo_producto_id: this.filters.tipo_producto_id || undefined,
          precio_min: this.filters.precio_min || undefined,
          precio_max: this.filters.precio_max || undefined,
          fecha_ingreso: this.filters.fecha_ingreso || undefined
        };
        
        const { data } = await axios.get('/productos', { 
          params,
          headers: {
            'Accept': 'application/json'
          }
        });
        this.productos = data.data || [];
      } catch (error) {
        console.error('Error cargando productos:', error);
        this.error = "Error al cargar productos";
        throw error;
      }
    },
    aplicarFiltros() {
      this.cargarProductos();
    },
    resetFilters() {
      this.filters = {
        id: null,
        nombre: '',
        tipo_producto_id: null,
        precio_min: null,
        precio_max: null,
        fecha_ingreso: ''
      };
      this.cargarProductos();
    },
    formatDate(date) {
      if (!date) return '';
      try {
        return new Date(date).toLocaleDateString('es-ES');
      } catch {
        return date;
      }
    },
    exportarProductos() {
      this.isExporting = true; 
      this.errorMessage = "";
     
      const link = document.createElement('a');
      link.href = '/exportar-productos';
      link.setAttribute('download', 'prodcutos.xlsx'); 
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
}
</script>

<style scoped>
.container-center {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

.table-responsive {
  max-height: 60vh;
  overflow-y: auto;
}

.filter-section {
  background-color: #f8fafc;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
</style>