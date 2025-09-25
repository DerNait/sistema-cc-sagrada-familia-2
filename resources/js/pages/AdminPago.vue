<template>
  <div class="container-fluid p-4">
    <!-- Contenedor principal blanco -->
    <div class="card border-0 shadow-sm bg-light">
      <div class="card-body p-4">
        <!-- Título -->
        <div class="row mb-4">
          <div class="col-12">
            <h2 class="fw-bold text-dark mb-0">Pagos</h2>
          </div>
        </div>

        <!-- Dropdown de filtro -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
              <div class="dropdown">
                <select 
                  v-model="selectedFilter" 
                  class="form-select" 
                  style="width: auto;"
                >
                  <option value="estudiante">Estudiante</option>
                  <option value="profesor">Profesor</option>
                  <option value="admin">Administrador</option>
                </select>
              </div>
              <div class="position-relative">
                <input 
                  v-model="searchQuery"
                  type="search" 
                  class="form-control" 
                  placeholder="Buscar" 
                  style="width: 300px;"
                />
                <i class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabla usando SortableTable -->
        <div class="row">
          <div class="col-12">
            <SortableTable
              :columns="tableColumns"
              :rows="filteredRows"
              :page-lengths="[10, 25, 50, 100, -1]"
            >
              <!-- Slot personalizado para las acciones -->
              <template #row-actions="{ row }">
                <div class="d-flex gap-1 justify-content-center">
                  <!-- Botón Ver -->
                  <button 
                    class="btn btn-outline-dark btn-sm rounded-circle"
                    @click="viewPayment(row)"
                    title="Ver detalles"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <!-- Botón Editar -->
                  <button 
                    class="btn btn-outline-success btn-sm rounded-circle"
                    @click="editPayment(row)"
                    title="Editar"
                  >
                    <i class="fas fa-check"></i>
                  </button>
                  <!-- Botón Eliminar -->
                  <button 
                    class="btn btn-outline-danger btn-sm rounded-circle"
                    @click="deletePayment(row)"
                    title="Eliminar"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </template>
            </SortableTable>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para mostrar comprobante -->
    <div 
      v-if="showComprobanteModal" 
      class="modal fade show d-block" 
      tabindex="-1" 
      style="background-color: rgba(0,0,0,0.5);"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <!-- Sin header/título -->
          <button 
            type="button" 
            class="btn-close position-absolute top-0 end-0 m-3" 
            style="z-index: 1050;"
            @click="closeComprobanteModal"
          ></button>
          
          <div class="modal-body p-0 text-center">
            <div v-if="selectedPayment?.comprobante" class="comprobante-container">
              <img 
                :src="selectedPayment.comprobante" 
                :alt="`Comprobante de ${selectedPayment.nombre} ${selectedPayment.apellido}`"
                class="img-fluid w-100"
                style="max-height: 70vh; object-fit: contain;"
                @error="imageError = true"
              />
              <div v-if="imageError" class="alert alert-warning m-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error al cargar la imagen del comprobante
              </div>
            </div>
            <div v-else class="alert alert-info m-3">
              <i class="fas fa-info-circle me-2"></i>
              No hay comprobante disponible para este pago
            </div>
          </div>
          
          <!-- Botones centrados sin footer -->
          <div class="d-flex justify-content-center gap-3 p-4" v-if="selectedPayment?.comprobante">
            <button 
              type="button"
              class="btn btn-success btn-lg px-4 py-3 action-button-rect"
              @click="approvePayment"
            >
              <i class="fas fa-clipboard-check me-2"></i>
              Aprobar
            </button>
            <button 
              type="button"
              class="btn btn-danger btn-lg px-4 py-3 action-button-rect"
              @click="rejectPayment"
            >
              <i class="fas fa-times me-2"></i>
              Rechazar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import SortableTable from '@/components/SortableTable.vue'

// Recibir los datos del controller Laravel
const props = defineProps({
  pagos: {
    type: Array,
    default: () => []
  }
})

// Estado reactivo
const selectedFilter = ref('estudiante')
const searchQuery = ref('')

// Variables para el modal del comprobante
const showComprobanteModal = ref(false)
const selectedPayment = ref(null)
const imageError = ref(false)

// Usar los datos que vienen del controller
const paymentsData = ref(props.pagos)

// Definición de columnas para la tabla
const tableColumns = {
  id: {
    label: 'ID',
    field: 'id',
    visible: true,
    filterType: 'numeric'
  },
  nombre: {
    label: 'Nombre',
    field: 'nombre',
    visible: true,
    filterType: 'text'
  },
  apellido: {
    label: 'Apellido',
    field: 'apellido',
    visible: true,
    filterType: 'text'
  },
  correo: {
    label: 'Correo',
    field: 'correo',
    visible: true,
    filterType: 'text'
  },
  meses_pagados: {
    label: 'Meses pagados',
    field: 'meses_pagados',
    visible: true,
    filterType: 'numeric'
  },
  fecha_registro: {
    label: 'Fecha registro',
    field: 'fecha_registro',
    visible: true,
    filterType: 'date'
  },
  fecha_nacimiento: {
    label: 'Fecha nacimiento',
    field: 'fecha_nacimiento',
    visible: true,
    filterType: 'date'
  }
}

// Computed para filtrar los datos
const filteredRows = computed(() => {
  let filtered = paymentsData.value

  // Filtrar por búsqueda
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(row => 
      row.nombre.toLowerCase().includes(query) ||
      row.apellido.toLowerCase().includes(query) ||
      row.correo.toLowerCase().includes(query)
    )
  }

  return filtered
})

// Métodos para las acciones de la tabla
const viewPayment = (row) => {
  selectedPayment.value = row
  imageError.value = false
  showComprobanteModal.value = true
}

const closeComprobanteModal = () => {
  showComprobanteModal.value = false
  selectedPayment.value = null
  imageError.value = false
}

const approvePayment = () => {
  if (!selectedPayment.value) return
  
  if (confirm(`¿Estás seguro de aprobar el pago de ${selectedPayment.value.nombre} ${selectedPayment.value.apellido}?`)) {
    console.log('Aprobar pago:', selectedPayment.value)
    
    closeComprobanteModal()
    
    
  }
}

const rejectPayment = () => {
  if (!selectedPayment.value) return
  
  if (confirm(`¿Estás seguro de rechazar el pago de ${selectedPayment.value.nombre} ${selectedPayment.value.apellido}?`)) {
    console.log('Rechazar pago:', selectedPayment.value)
   
    
    // Cerrar modal después de rechazar
    closeComprobanteModal()
    
  }
}

const editPayment = (row) => {
  console.log('Editar pago:', row)
}

const deletePayment = (row) => {
  console.log('Eliminar pago:', row)
  if (confirm(`¿Estás seguro de eliminar el pago de ${row.nombre} ${row.apellido}?`)) {
    const index = paymentsData.value.findIndex(item => item.id === row.id)
    if (index > -1) {
      paymentsData.value.splice(index, 1)
    }
  }
}
</script>

<style scoped>

.btn-sm.rounded-circle {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}


.card {
  border-radius: 12px;
  background-color: rgba(248, 249, 250, 0.4) !important;
  backdrop-filter: blur(8px); /* efecto frosted glass */
}

.form-select:focus,
.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}


.form-select {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  padding: 0.5rem 2.5rem 0.5rem 0.75rem;
}


.form-control {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  padding: 0.5rem 2.5rem 0.5rem 0.75rem;
}


.modal.show {
  display: block !important;
}

.comprobante-container img {
  border: none;
  box-shadow: none;
}

/* Botones de acción del modal rectangulares */
.action-button-rect {
  border-radius: 25px;
  font-weight: 600;
  font-size: 1.1rem;
  min-width: 150px;
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.2s ease;
}

.action-button-rect:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.action-button-rect i {
  font-size: 1.2rem;
}

/* Modal sin bordes redondeados en la imagen */
.modal-content {
  border-radius: 12px;
  overflow: hidden;
}
</style>