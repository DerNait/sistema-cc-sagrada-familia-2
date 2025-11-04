<template>
  <div class="crud-container">
    <div class="m-3 d-flex justify-content-between align-items-center">
      <h4 class="fw-semibold">Pagos</h4>
    </div>

    <div class="filters-container px-3 py-4">
      <div id="filters" class="d-flex flex-wrap justify-content-center align-items-end gap-4">
        <!-- Filtro por estudiante -->
        <div>
          <p class="fw-bold mb-1 fs-6 ms-1">Estudiante</p>
          <Filtros
            v-model="filtros.estudiante_id"
            :options="estudianteOptions"
            value-key="id"
            label-key="nombre_completo"
            placeholder="Buscar estudiante…"
          />
        </div>

        <!-- Filtro por tipo de estado -->
        <div>
          <p class="fw-bold mb-1 fs-6 ms-1">Tipo</p>
          <Filtros
            v-model="filtros.tipo_estado_id"
            :options="tipoEstadoOptions"
            value-key="id"
            label-key="tipo"
            placeholder="Estado del pago…"
          />
        </div>

        <!-- Filtro de fecha desde -->
        <div>
          <p class="fw-bold mb-1 fs-6 ms-1">Desde</p>
          <DateFiltro v-model="filtros.fecha_inicio" placeholder="dd/mm/aaaa" />
        </div>

        <!-- Filtro de fecha hasta -->
        <div>
          <p class="fw-bold mb-1 fs-6 ms-1">Hasta</p>
          <DateFiltro v-model="filtros.fecha_fin" placeholder="dd/mm/aaaa" />
        </div>

        <!-- Botón limpiar filtros -->
        <div v-if="hasActiveFilters">
          <button 
            class="btn btn-outline-secondary" 
            @click="limpiarFiltros"
            title="Limpiar todos los filtros"
          >
            <i class="fa-solid fa-filter-circle-xmark me-1"></i>
            Limpiar
          </button>
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
import DateFiltro from '@/components/DateFiltro.vue'

// Recibir los datos del controller Laravel
const props = defineProps({
  pagos: {
    type: Array,
    default: () => []
  },
  tipos_estado: {
    type: Array,
    default: () => []
  }
})

// Estado reactivo
const selectedFilter = ref('estudiante')
const isProcessing = ref(false)

// Variables para el modal del comprobante
const showComprobanteModal = ref(false)
const selectedPayment = ref(null)
const imageError = ref(false)

// Usar los datos que vienen del controller
const paymentsData = ref(props.pagos)

// ESTADO DE FILTROS (con tipo_estado_id)
const filtros = ref({
  estudiante_id: null,
  tipo_estado_id: null,
  fecha_inicio: '',
  fecha_fin: ''
})

// OPCIONES para el filtro de estudiantes
const estudianteOptions = computed(() => {
  // Extraer estudiantes únicos de los pagos
  const estudiantesUnicos = new Map()
  
  paymentsData.value.forEach(pago => {
    const id = pago.id // o el campo que uses para identificar al estudiante
    const nombreCompleto = `${pago.nombre} ${pago.apellido}`.trim()
    
    if (!estudiantesUnicos.has(id)) {
      estudiantesUnicos.set(id, {
        id: id,
        nombre_completo: nombreCompleto
      })
    }
  })
  
  return Array.from(estudiantesUnicos.values()).sort((a, b) => 
    a.nombre_completo.localeCompare(b.nombre_completo)
  )
})

// OPCIONES para el filtro de tipos de estado
const tipoEstadoOptions = computed(() => {
  return props.tipos_estado || []
})

// COMPUTED para detectar filtros activos (con tipo_estado_id)
const hasActiveFilters = computed(() => {
  return filtros.value.estudiante_id !== null || 
         filtros.value.tipo_estado_id !== null ||
         filtros.value.fecha_inicio !== '' || 
         filtros.value.fecha_fin !== ''
})

// FUNCIÓN para limpiar filtros (con tipo_estado_id)
function limpiarFiltros() {
  filtros.value.estudiante_id = null
  filtros.value.tipo_estado_id = null
  filtros.value.fecha_inicio = ''
  filtros.value.fecha_fin = ''
}

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

// LÓGICA DE FILTRADO SIMPLIFICADA (sin búsqueda por texto)
const filteredRows = computed(() => {
  let filtered = paymentsData.value

  // 1) Filtro por estudiante
  if (filtros.value.estudiante_id !== null) {
    filtered = filtered.filter(row => row.id === filtros.value.estudiante_id)
  }

  // 2) Filtro por tipo de estado
  if (filtros.value.tipo_estado_id !== null) {
    filtered = filtered.filter(row => row.tipo_estado_id === filtros.value.tipo_estado_id)
  }

  // 3) Filtro por rango de fechas
  if (filtros.value.fecha_inicio) {
    filtered = filtered.filter(row => {
      const fechaPago = row.fecha_registro ? String(row.fecha_registro).slice(0, 10) : null
      return fechaPago && fechaPago >= filtros.value.fecha_inicio
    })
  }

  if (filtros.value.fecha_fin) {
    filtered = filtered.filter(row => {
      const fechaPago = row.fecha_registro ? String(row.fecha_registro).slice(0, 10) : null
      return fechaPago && fechaPago <= filtros.value.fecha_fin
    })
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
