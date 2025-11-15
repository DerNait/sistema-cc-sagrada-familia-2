<!-- resources/js/components/inventario-historial.vue -->
<template>
  <div class="container py-5">
    <h1 class="text-center mb-4 text-success fw-bold">
      📦 Historial Completo de Movimientos de Inventario
    </h1>

    <!-- Top bar: Search + botón Filtros -->
    <div class="d-flex align-items-center gap-2 mb-2">
      <SearchBar
        v-model="query"
        class="flex-grow-1"
        placeholder="Buscar por ID, producto, tipo, usuario o descripción…"
      />
      <button
        class="btn btn-outline-secondary"
        data-bs-toggle="offcanvas"
        data-bs-target="#filtersCanvas"
      >
        <i class="fa-solid fa-sliders"></i>
        <span class="ms-1">Filtros</span>
        <span v-if="activeCount" class="badge bg-success ms-1">{{ activeCount }}</span>
      </button>
    </div>

    <!-- Chips de filtros activos -->
    <div v-if="activeCount" class="mb-3 d-flex flex-wrap gap-2">
      <span v-for="chip in activeChips" :key="chip.key" class="badge rounded-pill text-bg-light">
        {{ chip.label }}:
        <strong>{{ chip.value }}</strong>
        <button class="btn btn-sm btn-link ms-1 p-0" @click="chip.clear" title="Quitar">✕</button>
      </span>
      <button class="btn btn-sm btn-outline-secondary" @click="clearAll">Limpiar todo</button>
    </div>

    <!-- Tabla -->
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

    <!-- Offcanvas con filtros -->
        <!-- Offcanvas con filtros -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="filtersCanvas">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filtros</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>

      <div class="offcanvas-body">
        <div class="filters-grid">
          <!-- Fila 1: Producto | Tipo -->
          <Filtros
            v-model="filtros.productoId"
            :options="productoOpts"
            value-key="id"
            label-key="nombre"
            placeholder="Producto"
            class="grid-producto"
          />
          <Filtros
            v-model="filtros.tipoMovimientoId"
            :options="tipoMovimientoOpts"
            value-key="id"
            label-key="tipo"
            placeholder="Tipo de movimiento"
            class="grid-tipo"
          />

          <!-- Fila 2: Usuario (a lo ancho) -->
          <Filtros
            v-model="filtros.usuarioId"
            :options="usuarioOpts"
            value-key="id"
            label-key="name"
            placeholder="Usuario"
            class="grid-usuario"
          />

          <!-- Fila 3: Cantidad (a lo ancho, con Min/Max dentro) -->
          <div class="field-block grid-cantidad">
            <label class="form-label fw-semibold small text-secondary mb-1 d-block">
              Cantidad
            </label>
            <div class="pair-grid">
              <Filtros v-model="filtros.cantidadMin" mode="numeric" placeholder="Min" />
              <Filtros v-model="filtros.cantidadMax" mode="numeric" placeholder="Max" />
            </div>
          </div>

          <!-- Fila 4: Fecha (a lo ancho, con Desde/Hasta dentro) -->
          <div class="field-block grid-fecha">
            <label class="form-label fw-semibold small text-secondary mb-1 d-block">
              Fecha
            </label>
            <div class="pair-grid">
              <Filtros v-model="filtros.fechaDesde" mode="text" placeholder="Desde (YYYY-MM-DD)" />
              <Filtros v-model="filtros.fechaHasta" mode="text" placeholder="Hasta (YYYY-MM-DD)" />
            </div>
          </div>

          <!-- Fila 5: Botones (a lo ancho) -->
          <div class="actions-row grid-actions">
            <button class="btn btn-success w-100" data-bs-dismiss="offcanvas">Aplicar</button>
            <button class="btn btn-outline-secondary w-100" @click="clearAll">Limpiar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import SortableTable from '../components/SortableTable.vue'
import SearchBar from '../components/SearchBar.vue'
import Filtros from '../components/Filtros.vue'

const props = defineProps({
  movimientos: { type: Array, default: () => [] }
})

/* ===== Columnas para SortableTable ===== */
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

/* ===== Filas “crudas” ===== */
const rows = computed(() =>
  (props.movimientos ?? []).map(m => ({
    id: m.id,
    producto: m.producto ?? null,                 // { id, nombre }
    tipo_movimiento: m.tipo_movimiento ?? null,   // { id, tipo }
    cantidad: m.cantidad,
    stock_pre: m.stock_pre,
    stock_post: m.stock_post,
    usuario: m.usuario ?? null,                   // { id, name }
    descripcion: m.descripcion ?? '',
    fecha: m.fecha,
  }))
)

/* ===== Opciones únicas para selects ===== */
function uniqueBy(arr, getKey) {
  const map = new Map()
  for (const item of arr) {
    const key = getKey(item)
    if (key != null && !map.has(key)) map.set(key, item)
  }
  return Array.from(map.values())
}

const productoOpts = computed(() => {
  const data = rows.value.map(r => r.producto).filter(Boolean)
  const uniques = uniqueBy(data, x => x.id)
  return uniques.sort((a, b) => (a?.nombre ?? '').localeCompare(b?.nombre ?? ''))
})

const tipoMovimientoOpts = computed(() => {
  const data = rows.value.map(r => r.tipo_movimiento).filter(Boolean)
  const uniques = uniqueBy(data, x => x.id)
  return uniques.sort((a, b) => (a?.tipo ?? '').localeCompare(b?.tipo ?? ''))
})

const usuarioOpts = computed(() => {
  const data = rows.value.map(r => r.usuario).filter(Boolean)
  const uniques = uniqueBy(data, x => x.id)
  return uniques.sort((a, b) => (a?.name ?? '').localeCompare(b?.name ?? ''))
})

/* ===== Búsqueda libre ===== */
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
]

/* ===== Estado de filtros ===== */
const filtros = ref({
  productoId: '',
  tipoMovimientoId: '',
  usuarioId: '',
  cantidadMin: '',
  cantidadMax: '',
  fechaDesde: '',
  fechaHasta: '',
})

/* ===== Helpers ===== */
function parseMaybeDate(v) {
  if (!v) return null
  const d = new Date(v)
  return isNaN(d.getTime()) ? null : d
}
function isNumberLike(v) {
  return v !== '' && v !== null && typeof v !== 'undefined' && !isNaN(Number(v))
}

/* ===== Filtrado final ===== */
const filteredRows = computed(() => {
  const q = query.value.trim().toLowerCase()
  const f = filtros.value
  const fDesde = parseMaybeDate(f.fechaDesde)
  const fHasta = parseMaybeDate(f.fechaHasta)

  return rows.value.filter(r => {
    // 1) búsqueda libre
    if (q) {
      const hit = searchableFields.some(field => {
        const val = getValue(r, field)
        return (val ?? '').toString().toLowerCase().includes(q)
      })
      if (!hit) return false
    }

    // 2) filtros select
    if (f.productoId && r.producto?.id !== f.productoId) return false
    if (f.tipoMovimientoId && r.tipo_movimiento?.id !== f.tipoMovimientoId) return false
    if (f.usuarioId && r.usuario?.id !== f.usuarioId) return false

    // 3) filtros numéricos
    if (isNumberLike(f.cantidadMin) && Number(r.cantidad) < Number(f.cantidadMin)) return false
    if (isNumberLike(f.cantidadMax) && Number(r.cantidad) > Number(f.cantidadMax)) return false

    // 4) fechas (incluyente)
    if (fDesde || fHasta) {
      const d = parseMaybeDate(r.fecha)
      if (!d) return false
      if (fDesde && d < fDesde) return false
      if (fHasta && d > fHasta) return false
    }

    return true
  })
})

/* ===== Chips y acciones ===== */
const activeCount = computed(() => {
  const f = filtros.value
  return [
    f.productoId, f.tipoMovimientoId, f.usuarioId,
    f.cantidadMin, f.cantidadMax, f.fechaDesde, f.fechaHasta
  ].filter(v => v !== '' && v !== null && v !== undefined).length
})

const activeChips = computed(() => {
  const f = filtros.value
  const chips = []

  if (f.productoId) {
    const o = productoOpts.value.find(o => o.id === f.productoId)
    chips.push({ key:'prod', label:'Producto', value:o?.nombre ?? f.productoId, clear: () => f.productoId = '' })
  }
  if (f.tipoMovimientoId) {
    const o = tipoMovimientoOpts.value.find(o => o.id === f.tipoMovimientoId)
    chips.push({ key:'tipo', label:'Tipo', value:o?.tipo ?? f.tipoMovimientoId, clear: () => f.tipoMovimientoId = '' })
  }
  if (f.usuarioId) {
    const o = usuarioOpts.value.find(o => o.id === f.usuarioId)
    chips.push({ key:'user', label:'Usuario', value:o?.name ?? f.usuarioId, clear: () => f.usuarioId = '' })
  }
  if (isNumberLike(f.cantidadMin) || isNumberLike(f.cantidadMax)) {
    chips.push({
      key:'cant', label:'Cantidad',
      value:`${f.cantidadMin || '—'}–${f.cantidadMax || '—'}`,
      clear: () => { f.cantidadMin=''; f.cantidadMax='' }
    })
  }
  if (f.fechaDesde || f.fechaHasta) {
    chips.push({
      key:'fecha', label:'Fecha',
      value:`${f.fechaDesde || '—'} → ${f.fechaHasta || '—'}`,
      clear: () => { f.fechaDesde=''; f.fechaHasta='' }
    })
  }
  return chips
})

function clearAll () {
  Object.assign(filtros.value, {
    productoId:'', tipoMovimientoId:'', usuarioId:'',
    cantidadMin:'', cantidadMax:'', fechaDesde:'', fechaHasta:''
  })
}

/* ===== Utilidades visuales ===== */
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
/* ===== Offcanvas: ancho cómodo por breakpoint ===== */
.offcanvas.offcanvas-end {
  --bs-offcanvas-width: 360px; /* base móvil */
}
@media (min-width: 576px) {
  .offcanvas.offcanvas-end { --bs-offcanvas-width: 420px; }
}
@media (min-width: 768px) {
  .offcanvas.offcanvas-end { --bs-offcanvas-width: 480px; }
}

/* ===== Grid de filtros dentro del offcanvas ===== */
.filters-grid {
  display: grid;
  grid-template-columns: 1fr;  /* móvil: 1 columna */
  gap: 1rem;
}

/* Layout de 2 columnas y áreas a partir de 576px */
@media (min-width: 576px) {
  .filters-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    grid-template-areas:
      "producto tipo"
      "usuario usuario"
      "cantidad cantidad"
      "fecha fecha"
      "actions actions";
  }

  .grid-producto { grid-area: producto; }
  .grid-tipo     { grid-area: tipo; }
  .grid-usuario  { grid-area: usuario; }
  .grid-cantidad { grid-area: cantidad; }
  .grid-fecha    { grid-area: fecha; }
  .grid-actions  { grid-area: actions; }
}

/* Pares (min–max / desde–hasta) responden con 1–2 columnas */
.pair-grid {
  display: grid;
  grid-template-columns: 1fr; 
  gap: .75rem;
}
@media (min-width: 576px) {
  .pair-grid { grid-template-columns: 1fr 1fr; }
}

/* Acciones: botones lado a lado en pantallas medias+ */
.actions-row {
  display: grid;
  grid-template-columns: 1fr;
  gap: .75rem;
}
@media (min-width: 576px) {
  .actions-row { grid-template-columns: 1fr 1fr; }
}


/* ===== Overrides para Filtros.vue dentro del offcanvas ===== */
/* elimina el min-width:280px del componente dentro del panel */
.offcanvas .filtro {
  min-width: 0 !important;
  width: 100% !important;
}

/* su dropdown que siga el ancho del contenedor y no sobrepase */
.offcanvas .filtro-menu {
  width: 100% !important;
  max-width: 100% !important;
  left: 0; right: auto;
}

/* input group sin desbordes */
.offcanvas .filtro-input { width: 100%; }

/* chips: que no rompan el layout */
.badge.text-bg-light {
  background-color: #f1f3f5 !important;
  border: 1px solid #e2e6ea;
}

.container { max-width: 1200px; }

/* que la barra superior se vea compacta y responsiva */
.btn .badge { vertical-align: middle; }

/* opcional: afinar la separación de las chips */
.badge.text-bg-light {
  background-color: #f1f3f5 !important;
  border: 1px solid #e2e6ea;
}
</style>
