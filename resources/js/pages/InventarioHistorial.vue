<!-- resources/js/components/inventario-historial.vue -->
<template>
  <div class="container py-5">
    <h1 class="text-center mb-4 text-success fw-bold">
      📦 Historial Completo de Movimientos de Inventario
    </h1>

    <!-- 🔎 Barra de búsqueda -->
    <SearchBar
      v-model="query"
      placeholder="Buscar por ID, producto, tipo, usuario o descripción…"
      class="mb-3"
    />

    <div class="shadow rounded-4 bg-white p-3">
      <sortable-table
        :columns="columns"
        :rows="filteredRows"
        :page-lengths="[10,25,50,100,-1]"
      >
        <template #cell-fecha="{ value }">
          {{ formatDate(value) }}
        </template>
        <template #cell-descripcion="{ value }">
          <span :title="value">{{ truncate(value, 120) }}</span>
        </template>
      </sortable-table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import SortableTable from '../components/SortableTable.vue'
import SearchBar from '../components/SearchBar.vue'   // ← aquí lo importas

const props = defineProps({
  movimientos: { type: Array, default: () => [] }
})

/* -------- columnas -------- */
const columns = {
  id: { field: 'id', label: 'ID', visible: true, filterType: 'numeric' },
  'producto.nombre': { field: 'producto.nombre', label: 'Producto', visible: true },
  'tipo_movimiento.tipo': { field: 'tipo_movimiento.tipo', label: 'Tipo Movimiento', visible: true },
  cantidad: { field: 'cantidad', label: 'Cantidad', visible: true, filterType: 'numeric' },
  stock_pre: { field: 'stock_pre', label: 'Stock Antes', visible: true, filterType: 'numeric' },
  stock_post: { field: 'stock_post', label: 'Stock Después', visible: true, filterType: 'numeric' },
  'usuario.name': { field: 'usuario.name', label: 'Usuario', visible: true },
  descripcion: { field: 'descripcion', label: 'Descripción', visible: true },
  fecha: { field: 'fecha', label: 'Fecha', visible: true, filterType: 'date' },
}

/* -------- filas “crudas” -------- */
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
    fecha: m.fecha,
  }))
)

/* -------- búsqueda -------- */
const query = ref('')

function getValue (obj, path) {
  return path.split('.').reduce((o, p) => (o ? o[p] : null), obj)
}

const searchableFields = [
  'id',
  'producto.nombre',
  'tipo_movimiento.tipo',
  'usuario.name',
  'descripcion',
  // agrega 'fecha' si quieres matchear por fecha textual
]

const filteredRows = computed(() => {
  const q = query.value.trim().toLowerCase()
  if (!q) return rows.value

  return rows.value.filter(r =>
    searchableFields.some(f => {
      const v = getValue(r, f)
      return (v ?? '').toString().toLowerCase().includes(q)
    })
  )
})

/* -------- utilidades -------- */
function formatDate(value) {
  if (!value) return '—'
  try { return new Date(value).toLocaleString() } catch { return String(value) }
}
function truncate(text, len = 120) {
  if (!text) return '—'
  return text.length > len ? text.slice(0, len) + '…' : text
}
</script>

<style scoped>
.container { max-width: 1200px; }
</style>
