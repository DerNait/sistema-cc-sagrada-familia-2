<!-- resources/js/components/inventario-historial.vue -->
<template>
  <div class="container py-5">
    <h1 class="text-center mb-4 text-success fw-bold">
      📦 Historial Completo de Movimientos de Inventario
    </h1>

    <div class="shadow rounded-4 bg-white p-3">
      <sortable-table
        :columns="columns"
        :rows="rows"
        :page-lengths="[10,25,50,100,-1]"
      >
        <!-- Formato lindo para la fecha -->
        <template #cell-fecha="{ value }">
          {{ formatDate(value) }}
        </template>

        <!-- Si quieres truncar descripción con tooltip -->
        <template #cell-descripcion="{ value }">
          <span :title="value">
            {{ truncate(value, 120) }}
          </span>
        </template>
      </sortable-table>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import SortableTable from '../components/SortableTable.vue'

const props = defineProps({
  movimientos: { type: Array, default: () => [] }
})

/**
 * Mapeamos columnas para SortableTable.
 * Clave del objeto = "field" (puede ser path con punto)
 * visible: mostrar/ocultar
 * label: encabezado
 * filterType: 'numeric' | 'date' | (texto por defecto)
 */
const columns = {
  id:              { field: 'id',              label: 'ID',              visible: true,  filterType: 'numeric' },
  'producto.nombre': { field: 'producto.nombre', label: 'Producto',        visible: true },
  'tipo_movimiento.tipo': { field: 'tipo_movimiento.tipo', label: 'Tipo Movimiento', visible: true },
  cantidad:        { field: 'cantidad',        label: 'Cantidad',        visible: true,  filterType: 'numeric' },
  stock_pre:       { field: 'stock_pre',       label: 'Stock Antes',     visible: true,  filterType: 'numeric' },
  stock_post:      { field: 'stock_post',      label: 'Stock Después',   visible: true,  filterType: 'numeric' },
  'usuario.name':  { field: 'usuario.name',    label: 'Usuario',         visible: true },
  descripcion:     { field: 'descripcion',     label: 'Descripción',     visible: true },
  fecha:           { field: 'fecha',           label: 'Fecha',           visible: true,  filterType: 'date' },
}

// Normalizamos filas: tu tabla ya soporta paths, solo cuidamos nulos
const rows = computed(() =>
  (props.movimientos ?? []).map(m => ({
    id: m.id,
    producto: m.producto ?? null,
    tipo_movimiento: m.tipo_movimiento ?? null,
    cantidad: m.cantidad,
    stock_pre: m.stock_pre,
    stock_post: m.stock_post,
    usuario: m.usuario ?? null,
    descripcion: m.descripcion ?? '',
    fecha: m.fecha, // ISO/fecha válida; el sort 'date' la maneja
  }))
)

function formatDate(value) {
  if (!value) return '—'
  try {
    return new Date(value).toLocaleString()
  } catch { return String(value) }
}

function truncate(text, len = 120) {
  if (!text) return '—'
  return text.length > len ? text.slice(0, len) + '…' : text
}
</script>

<style scoped>
.container { max-width: 1200px; }
</style>
