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
          <!-- Slot personalizado para la columna de Estado -->
          <template #column-tipo_estado_nombre="{ row }">
            <span
              :class="[
                'badge',
                row.tipo_estado_nombre === 'Completado' ? 'bg-success' :
                row.tipo_estado_nombre === 'Pendiente'  ? 'bg-warning text-dark' :
                row.tipo_estado_nombre === 'Cancelado'  ? 'bg-danger' :
                'bg-secondary'
              ]"
            >
              {{ row.tipo_estado_nombre }}
            </span>
          </template>

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
              <!-- Botón Aprobar -->
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

const props = defineProps({
  pagos: Array,
  tipos_estado: Array
})

const selectedFilter = ref('estudiante')
const isProcessing = ref(false)
const showComprobanteModal = ref(false)
const selectedPayment = ref(null)
const imageError = ref(false)

const paymentsData = ref(props.pagos)

const filtros = ref({
  estudiante_id: null,
  tipo_estado_id: null,
  fecha_inicio: '',
  fecha_fin: ''
})

const estudianteOptions = computed(() => {
  const estudiantesUnicos = new Map()
  paymentsData.value.forEach(pago => {
    const id = pago.id
    const nombreCompleto = `${pago.nombre} ${pago.apellido}`.trim()
    if (!estudiantesUnicos.has(id)) {
      estudiantesUnicos.set(id, { id, nombre_completo: nombreCompleto })
    }
  })
  return Array.from(estudiantesUnicos.values()).sort((a, b) => a.nombre_completo.localeCompare(b.nombre_completo))
})

const tipoEstadoOptions = computed(() => props.tipos_estado || [])

const hasActiveFilters = computed(() =>
  filtros.value.estudiante_id !== null ||
  filtros.value.tipo_estado_id !== null ||
  filtros.value.fecha_inicio !== '' ||
  filtros.value.fecha_fin !== ''
)

function limpiarFiltros() {
  filtros.value.estudiante_id = null
  filtros.value.tipo_estado_id = null
  filtros.value.fecha_inicio = ''
  filtros.value.fecha_fin = ''
}

const getCsrfToken = () =>
  document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

const tableColumns = {
  id: { label: 'ID', field: 'id', visible: true },
  nombre: { label: 'Nombre', field: 'nombre', visible: true },
  apellido: { label: 'Apellido', field: 'apellido', visible: true },
  correo: { label: 'Correo', field: 'correo', visible: true },
  monto_pagado: { label: 'Monto pagado', field: 'monto_pagado', visible: true },
  meses_pagados: { label: 'Meses pagados', field: 'meses_pagados', visible: true },
  periodo_inicio: { label: 'Periodo inicio', field: 'periodo_inicio', visible: true },
  periodo_fin: { label: 'Periodo fin', field: 'periodo_fin', visible: true },
  fecha_registro: { label: 'Fecha registro', field: 'fecha_registro', visible: true },
  tipo_estado_nombre: { label: 'Estado del pago', field: 'tipo_estado_nombre', visible: true } // 🟢 NUEVO
}

const filteredRows = computed(() => {
  let filtered = paymentsData.value
  if (filtros.value.estudiante_id !== null)
    filtered = filtered.filter(row => row.id === filtros.value.estudiante_id)
  if (filtros.value.tipo_estado_id !== null)
    filtered = filtered.filter(row => row.tipo_estado_id === filtros.value.tipo_estado_id)
  if (filtros.value.fecha_inicio)
    filtered = filtered.filter(row => row.fecha_registro?.slice(0, 10) >= filtros.value.fecha_inicio)
  if (filtros.value.fecha_fin)
    filtered = filtered.filter(row => row.fecha_registro?.slice(0, 10) <= filtros.value.fecha_fin)
  return filtered
})

// Métodos (sin cambios)
const viewPayment = (row) => { selectedPayment.value = row; imageError.value = false; showComprobanteModal.value = true }
const closeComprobanteModal = () => { showComprobanteModal.value = false; selectedPayment.value = null; imageError.value = false; isProcessing.value = false }
// ... (resto de quickApprovePayment, approvePayment, deletePayment igual que antes)
</script>

<style scoped>
.badge {
  font-size: 0.85rem;
  padding: 0.4em 0.6em;
  border-radius: 0.5em;
}
</style>
