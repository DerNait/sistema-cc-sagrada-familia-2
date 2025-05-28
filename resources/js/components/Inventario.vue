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

      <!-- Sección de descarga -->
      <div class="download-section mt-5 text-center">
        <h4 class="mb-3">Descargar Inventario</h4>
        <p class="text-muted mb-4">Haz clic en el botón para descargar la planilla de Inventario en formato Excel.</p>
        
        <button
          class="btn btn-success"
          @click="exportarProductos"
          :disabled="isExporting"
        >
          <span v-if="!isExporting">Descargar Inventario en Excel</span>
          <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <span v-if="isExporting"> Generando archivo...</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2';
import axios from 'axios';

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
      isExporting: false
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
        await Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron cargar los datos iniciales',
          footer: 'Por favor intenta nuevamente'
        });
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
        await Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron cargar los tipos de productos',
        });
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
        await Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron cargar los productos',
        });
        console.error('Error cargando productos:', error);
        throw error;
      }
    },
    async aplicarFiltros() {
      try {
        await this.cargarProductos();
        await Swal.fire({
          title: 'Filtros aplicados',
          text: `Se encontraron ${this.productos.length} productos`,
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
      } catch (error) {
        console.error(error);
      }
    },
    async resetFilters() {
      this.filters = {
        id: null,
        nombre: '',
        tipo_producto_id: null,
        precio_min: null,
        precio_max: null,
        fecha_ingreso: ''
      };
      try {
        await this.cargarProductos();
        await Swal.fire({
          title: 'Filtros limpiados',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false
        });
      } catch (error) {
        console.error(error);
      }
    },
    formatDate(date) {
      if (!date) return '';
      try {
        return new Date(date).toLocaleDateString('es-ES');
      } catch {
        return date;
      }
    },
    async exportarProductos() {
      this.isExporting = true;
      
      try {
        // Simulamos un retraso para mostrar el spinner
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        const link = document.createElement('a');
        link.href = '/exportar-productos';
        link.setAttribute('download', 'inventario.xlsx'); 
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        await Swal.fire({
          title: 'Descarga completada',
          text: 'El inventario se ha descargado correctamente',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
      } catch (error) {
        await Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo descargar el inventario',
        });
        console.error(error);
      } finally {
        this.isExporting = false;
      }
    }
  }
}
</script>

<style scoped>
body {
  font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, sans-serif;
}

.filter-section {
  background-color: #f8f9fa;
  border-radius: 0.375rem;
}

.table-responsive {
  max-height: 60vh;
  overflow-y: auto;
}

.download-section {
  padding: 1.5rem;
  border-top: 1px solid #dee2e6;
}

.btn-success {
  padding: 0.5rem 1.5rem;
  font-size: 1.1rem;
}

.spinner-border {
  vertical-align: middle;
  margin-right: 0.5rem;
}
</style>