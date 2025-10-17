<template>
  <div class="crud-container">
    <div class="m-3 d-flex justify-content-between align-items-center">
      <h4 class="fw-semibold">Pagos</h4>
    </div>

    <div class="filters-container d-flex px-3 py-4 d-flex justify-content-between align-items-center">
      <div id="filters" class="d-flex flex-wrap gap-2">
        <div>
          <Filtros
            :options="filtroOptions"
            value-key="id"
            label-key="nombre"
            placeholder="Rol"
          />
       </div>

        <!-- <div v-if="hasActiveFilters" class="align-self-end">
          <button class="btn btn-outline-secondary" @click="clearFilters">
            Limpiar
          </button>
        </div> -->
      </div>
  
      <div style="max-width: 270px; flex:1">
        <SearchBar v-model="searchQuery" />
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
            <div class="d-flex gap-1 justify-content-evenly">
              <!-- Botón Ver -->
              <button 
                class="btn btn-outline-secondary"
                @click="viewPayment(row)"
                title="Ver detalles"
              >
                <i class="fas fa-eye"></i>
              </button>
              <!-- Botón Aprobar (cheque) -->
              <button 
                class="btn btn-outline-primary"
                @click="quickApprovePayment(row)"
                title="Aprobar"
                :disabled="isProcessing"
              >
                <i class="fas fa-check"></i>
              </button>
              <!-- Botón Eliminar -->
              <button 
                class="btn btn-outline-danger"
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
            class="btn btn-primary btn-lg px-4 py-3 action-button-rect"
            @click="approvePayment"
            :disabled="isProcessing"
          >
            <i class="fas fa-save me-2"></i>
            {{ isProcessing ? 'Procesando...' : 'Aprobar' }}
          </button>
          <button 
            type="button"
            class="btn btn-danger btn-lg px-4 py-3 action-button-rect"
            @click="deletePayment(selectedPayment)"
            :disabled="isProcessing"
          >
            <i class="fas fa-times me-2"></i>
            {{ isProcessing ? 'Procesando...' : 'Rechazar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import SortableTable from '@/components/SortableTable.vue'
import Filtros from '@/components/Filtros.vue'
import SearchBar from '@/components/SearchBar.vue'

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
const isProcessing = ref(false)

// Variables para el modal del comprobante
const showComprobanteModal = ref(false)
const selectedPayment = ref(null)
const imageError = ref(false)

// Usar los datos que vienen del controller
const paymentsData = ref(props.pagos)

// Obtener CSRF token
const getCsrfToken = () => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (!token) {
    console.error('CSRF token no encontrado')
  }
  return token || ''
}

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
  monto_pagado: {
    label: 'Monto pagado',
    field: 'monto_pagado',
    visible: true,
    filterType: 'numeric'
  },
  meses_pagados: {
    label: 'Meses pagados',
    field: 'meses_pagados',
    visible: true,
    filterType: 'numeric'
  },
  periodo_inicio: {
    label: 'Periodo inicio',
    field: 'periodo_inicio',
    visible: true,
    filterType: 'date'
  },
  periodo_fin: {
    label: 'Periodo fin',
    field: 'periodo_fin',
    visible: true,
    filterType: 'date'
  },
  fecha_registro: {
    label: 'Fecha registro',
    field: 'fecha_registro',
    visible: true,
    filterType: 'date'
  },
}

const filtroOptions = [
  { id: 'estudiante', nombre: 'Estudiante' },
  { id: 'profesor', nombre: 'Profesor' },
  { id: 'admin', nombre: 'Administrador' }
]

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
  isProcessing.value = false
}

// Nueva función para aprobar directamente desde la tabla
const quickApprovePayment = async (row) => {
  if (isProcessing.value) return
  
  if (confirm(`¿Estás seguro de aprobar el pago de ${row.nombre} ${row.apellido}?`)) {
    isProcessing.value = true
    
    try {
      const formData = new FormData()
      formData.append('_method', 'PUT')
      formData.append('tipo_estado_id', '2') // 2 = Completado (aprobado)
      
      const response = await fetch(`/admin/pagos/${row.id}`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken()
        },
        body: formData
      })

      if (!response.ok) {
        const errorText = await response.text()
        console.error('Error response:', errorText)
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (data.success) {
        alert('Pago aprobado correctamente')
        window.location.reload()
      } else {
        alert('Error al aprobar el pago: ' + (data.message || 'Error desconocido'))
      }
    } catch (error) {
      console.error('Error completo:', error)
      alert('Error de conexión al aprobar el pago: ' + error.message)
    } finally {
      isProcessing.value = false
    }
  }
}

const approvePayment = async () => {
  if (!selectedPayment.value || isProcessing.value) return
  
  if (confirm(`¿Estás seguro de aprobar el pago de ${selectedPayment.value.nombre} ${selectedPayment.value.apellido}?`)) {
    isProcessing.value = true
    
    try {
      const formData = new FormData()
      formData.append('_method', 'PUT')
      formData.append('tipo_estado_id', '2') // 2 = Completado (aprobado)
      
      const response = await fetch(`/admin/pagos/${selectedPayment.value.id}`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken()
        },
        body: formData
      })

      if (!response.ok) {
        const errorText = await response.text()
        console.error('Error response:', errorText)
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (data.success) {
        alert('Pago aprobado correctamente')
        window.location.reload()
      } else {
        alert('Error al aprobar el pago: ' + (data.message || 'Error desconocido'))
      }
    } catch (error) {
      console.error('Error completo:', error)
      alert('Error de conexión al aprobar el pago: ' + error.message)
    } finally {
      isProcessing.value = false
      closeComprobanteModal()
    }
  }
}

/* const rejectPayment = async () => {
  if (!selectedPayment.value || isProcessing.value) return
  
  if (confirm(`¿Estás seguro de rechazar el pago de ${selectedPayment.value.nombre} ${selectedPayment.value.apellido}?`)) {
    isProcessing.value = true
    
    try {
      const formData = new FormData()
      formData.append('_method', 'PUT')
      formData.append('tipo_estado_id', '3') // 3 = Cancelado (rechazado)
      
      const response = await fetch(`/admin/pagos/${selectedPayment.value.id}`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken()
        },
        body: formData
      })

      if (!response.ok) {
        const errorText = await response.text()
        console.error('Error response:', errorText)
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (data.success) {
        alert('Pago rechazado correctamente')
        window.location.reload()
      } else {
        alert('Error al rechazar el pago: ' + (data.message || 'Error desconocido'))
      }
    } catch (error) {
      console.error('Error completo:', error)
      alert('Error de conexión al rechazar el pago: ' + error.message)
    } finally {
      isProcessing.value = false
      closeComprobanteModal()
    }
  }
} */

const editPayment = (row) => {
  console.log('Editar pago:', row)
  alert('Funcionalidad de edición en desarrollo')
}

const deletePayment = async (row) => {
  if (confirm(`¿Estás seguro de eliminar el pago de ${row.nombre} ${row.apellido}?`)) {
    try {
      const formData = new FormData()
      formData.append('_method', 'DELETE')
      
      const response = await fetch(`/admin/pagos/${row.id}`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken()
        },
        body: formData
      })

      if (!response.ok) {
        const errorText = await response.text()
        console.error('Error response:', errorText)
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (data.success) {
        alert('Pago eliminado correctamente')
        const index = paymentsData.value.findIndex(item => item.id === row.id)
        if (index > -1) {
          paymentsData.value.splice(index, 1)
        }
      } else {
        alert('Error al eliminar el pago: ' + (data.message || 'Error desconocido'))
      }
    } catch (error) {
      console.error('Error completo:', error)
      alert('Error de conexión al eliminar el pago: ' + error.message)
    }
  }
}
</script>

<style scoped>
.form-select:focus,
.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Mejorar el estilo del dropdown */
.form-select {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  padding: 0.5rem 2.5rem 0.5rem 0.75rem;
}

/* Mejorar el estilo del input de búsqueda */
.form-control {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  padding: 0.5rem 2.5rem 0.5rem 0.75rem;
}

/* Estilos para el modal del comprobante */
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

.action-button-rect:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.action-button-rect:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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