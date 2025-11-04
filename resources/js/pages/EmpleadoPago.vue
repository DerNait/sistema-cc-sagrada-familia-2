<template>
  <div class="crud-container">
    <div class="m-3 d-flex justify-content-between align-items-center">
      <h4 class="fw-semibold">Pagos de Empleados</h4>

      <div class="d-flex justify-content-evenly gap-2">
        <a
          class="btn btn-success"
          :href="`/admin/empleados/planilla?anio=${Number(localFilters.periodo_anio) || currentAnio}&mes=${Number(localFilters.periodo_mes) || currentMes}`"
          target="_blank"
        >
          <i class="fa-solid fa-file-download"></i>
          Descargar nómina
        </a>
      </div>
    </div>

    <!-- Filtros (Mes/Año) -->
    <div class="filters-container d-flex px-3 py-3 justify-content-between align-items-center">
      <div id="filters" class="d-flex flex-wrap gap-2">
        <div class="col-auto">
          <Filtros v-model="localFilters.periodo_mes" mode="select" :options="mesSelect" placeholder="Mes" />
        </div>
        <div class="col-auto">
          <Filtros v-model="localFilters.periodo_anio" mode="select" :options="anioSelect" placeholder="Año" />
        </div>

        <div v-if="hasActiveFilters" class="align-self-end">
          <button class="btn btn-outline-secondary" @click="resetFilters">Limpiar</button>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <SortableTable :columns="tableColumns" :rows="rowsFiltradas">
      <template #cell-tipo_estado_nombre="{ value }">
        <span :class="['badge', estadoBadge(value)]">{{ value }}</span>
      </template>

      <template #row-actions="{ row }">
        <div class="d-flex justify-content-evenly">
          <!-- Aprobar: siempre visible; si ya está Completado, lo deshabilito -->
          <button
            class="btn btn-sm btn-success"
            :disabled="row.tipo_estado_nombre === 'Completado'"
            @click="openApprove(row)"
            title="Aprobar / Registrar pago"
          >
            <i class="fa fa-check" />
          </button>

          <!-- Cancelar: solo si existe registro y está Completado -->
          <button
            v-if="row.id && row.tipo_estado_nombre === 'Completado'"
            class="btn btn-sm btn-danger"
            @click="cancelPayment(row)"
            title="Revertir a Pendiente"
          >
            <i class="fa fa-rotate-left" />
          </button>

          <!-- Ver / Editar: solo si existe registro -->
          <button
            v-if="row.id"
            class="btn btn-sm btn-outline-secondary"
            @click="openShow(row)"
            title="Ver"
          >
            <i class="fa-regular fa-eye" />
          </button>
          <button
            v-if="row.id"
            class="btn btn-sm btn-outline-primary"
            @click="openEdit(row)"
            title="Editar"
          >
            <i class="fa-regular fa-pen-to-square" />
          </button>
        </div>
      </template>
    </SortableTable>
</div>
<!-- Drawer Form -->
<transition name="backdrop">
  <div v-if="showForm"
     class="crud-backdrop d-flex justify-content-start"
     @click.self="close">
    <transition name="slide">
      <Form
        v-if="showForm"
        class="crud-drawer"
        :item="editingRow"
        :columns="formColumns"
        :action="formAction"
        :readonly="formMode === 'show'"
        :defaults="formDefaults"
        @saved="onSaved"
        @cancel="close"
      />
    </transition>
  </div>
</transition>

<!-- Busy -->
<transition name="backdrop">
  <div v-if="busy" class="crud-busy-backdrop d-flex align-items-center justify-content-center">
    <i class="fa-solid fa-spinner fa-spin fa-2xl text-white"></i>
  </div>
</transition>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

import SortableTable from '../components/SortableTable.vue';
import Filtros from '../components/Filtros.vue';
import Form from '../components/Crud/Form.vue';

const props = defineProps({
  rows: { type: Array, default: () => [] },              // ← NUEVO: viene ya mezclado (empleados + pagos del periodo)
  tipos_estado: { type: Array, default: () => [] },
  periodo: { type: Object, default: () => ({ mes: null, anio: null }) }, // ← default del backend
});

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
const baseUrl = '/pagos/empleado';

const busy       = ref(false);
const showForm   = ref(false);
const editingRow = ref(null);
const formAction = ref('');
const formMode   = ref('create');

const originalRows = ref([...(props.rows || [])]);

// Fechas (por si no viene periodo en props)
const now = new Date();
const currentMes  = props.periodo?.mes  ?? (now.getMonth() + 1);
const currentAnio = props.periodo?.anio ?? now.getFullYear();

// Filtros
const localFilters = reactive({
  periodo_mes:  String(currentMes),
  periodo_anio: String(currentAnio),
});
const hasActiveFilters = computed(() =>
  String(localFilters.periodo_mes) !== String(currentMes) ||
  String(localFilters.periodo_anio) !== String(currentAnio)
);

// Selects
const mesSelect = Array.from({ length: 12 }, (_, i) => {
  const nombre = new Date(0, i).toLocaleString('es', { month: 'long' });
  return { id: String(i + 1), nombre: nombre.charAt(0).toUpperCase() + nombre.slice(1) };
});
const aniosDetectados = new Set(originalRows.value.map(r => r.periodo_anio).filter(Boolean));
if (!aniosDetectados.size) aniosDetectados.add(currentAnio);
const anioSelect = Array.from(aniosDetectados).sort().map(a => ({ id: String(a), nombre: String(a) }));

// Columnas
const tableColumns = {
  nombre:              { field: 'nombre',          label: 'Nombre',           visible: true },
  apellido:            { field: 'apellido',        label: 'Apellido',         visible: true },
  periodo_label:       { field: 'periodo_label',   label: 'Período',          visible: true },
  salario_base:        { field: 'salario_base',        label: 'Salario Base', visible: true },
  total:               { field: 'total',           label: 'Total Neto',       visible: true },
  tipo_estado_nombre:  { field: 'tipo_estado_nombre', label: 'Estado',        visible: true },
};

// Form columns
const estadosMap = Object.fromEntries((props.tipos_estado || []).map(t => [String(t.id), t.tipo]));
const idPendiente   = Number(Object.entries(estadosMap).find(([,v]) => v === 'Pendiente')?.[0] ?? 0);
const idCompletado  = Number(Object.entries(estadosMap).find(([,v]) => v === 'Completado')?.[0] ?? 0);

const mesesMap = Object.fromEntries(mesSelect.map(m => [m.id, m.nombre]));
const aniosMap = Object.fromEntries(anioSelect.map(a => [a.id, a.nombre]));

const formColumns = {
  empleado_id:       { label: 'Empleado',           field: 'empleado_id',     type: 'numeric',  editable: false },
  periodo_mes:       { label: 'Mes',                field: 'periodo_mes',     type: 'relation', editable: false, options: mesesMap },
  periodo_anio:      { label: 'Año',                field: 'periodo_anio',    type: 'relation', editable: false, options: aniosMap },
  salario_base:      { label: 'Salario base',       field: 'salario_base',    type: 'numeric',  editable: true  },
  bonificacion_ley:  { label: 'Bonificación ley',   field: 'bonificacion_ley',type: 'numeric',  editable: true  },
  bonificacion_extra:{ label: 'Bonificación extra', field: 'bonificacion_extra', type: 'numeric', editable: true },
  descuento_igss:    { label: 'Descuento IGSS',     field: 'descuento_igss',  type: 'numeric',  editable: true  },
  descuentos_varios: { label: 'Descuentos varios',  field: 'descuentos_varios', type: 'numeric', editable: true },
  tipo_estado_id:    { label: 'Estado',             field: 'tipo_estado_id',  type: 'relation', editable: false, options: estadosMap },
  total:             { label: 'Total (solo lectura)', field: 'total',        type: 'numeric',  editable: false },
};

// Filtrado
const rowsFiltradas = computed(() => {
  const m = localFilters.periodo_mes;
  const a = localFilters.periodo_anio;
  return originalRows.value.filter(r =>
    (m ? String(r.periodo_mes)  === String(m) : true) &&
    (a ? String(r.periodo_anio) === String(a) : true)
  );
});

function resetFilters() {
  localFilters.periodo_mes  = String(currentMes);
  localFilters.periodo_anio = String(currentAnio);
}

// ==== Aprobar (crear o editar) ====
const formDefaults = ref({});
// ==== Aprobar (crear o editar) ====
async function openApprove(row) {
  // Caso 1: no existe registro -> crear (POST) con defaults
  if (!row.id) {
    formMode.value   = 'create';
    editingRow.value = null;
    formAction.value = baseUrl;
    formDefaults.value = {
      empleado_id:       row.empleado_id,
      periodo_mes:       String(localFilters.periodo_mes),
      periodo_anio:      String(localFilters.periodo_anio),
      salario_base:      Number(row.salario_base ?? 0),
      bonificacion_ley:  0,
      bonificacion_extra:0,
      descuento_igss:    0,
      descuentos_varios: 0,
      tipo_estado_id:    idCompletado, // ← crear en Completado
    };
    open();
    return;
  }

  // Caso 2: ya existe registro -> aprobar directo por PUT
  try {
    busy.value = true;
    const { data } = await axios.put(`${baseUrl}/${row.id}`, 
      { tipo_estado_id: idCompletado },
      { headers: { 'X-CSRF-TOKEN': csrf } }
    );
    busy.value = false;

    // Refrescar la fila en memoria
    const idx = originalRows.value.findIndex(r => r.id === row.id);
    if (idx > -1) {
      originalRows.value[idx] = {
        ...originalRows.value[idx],
        ...data.pago, // si tu update devuelve el pago completo
        tipo_estado_id: idCompletado,
        tipo_estado_nombre: 'Completado',
      };
    }

    showSuccess('Aprobado', 'Pago marcado como Completado');
  } catch (e) {
    busy.value = false;
    showError('Error', 'No se pudo aprobar el pago');
  }
}

// ==== Drawer / CRUD helpers ====
function open()  { showForm.value = true; document.body.style.overflow = 'hidden'; }
function close() { showForm.value = false; document.body.style.overflow = '';      }

async function loadLatest(id) {
  try {
    const { data } = await axios.get(`${baseUrl}/${id}`);
    return data;
  } catch {
    return originalRows.value.find(r => r.id === id);
  }
}

function openShow(row) {
  formMode.value = 'show';
  busy.value = true;
  loadLatest(row.id).then(record => {
    busy.value = false;
    editingRow.value = record;
    formAction.value = '';
    open();
  });
}

async function openEdit(row) {
  busy.value = true;
  try {
    formMode.value = 'edit';
    const fresh = await loadLatest(row.id);
    editingRow.value = fresh ?? { ...row };
    formAction.value = `${baseUrl}/${row.id}`;
    open();
  } finally {
    busy.value = false;
  }
}

async function onSaved(record) {
  // Si era create, ahora existe un id y debe pasar a Completado
  if (formMode.value === 'create') {
    const newId = record.id ?? record.pago_id;
    const newTotal = record.total ?? 0;

    const idx = originalRows.value.findIndex(r =>
      String(r.empleado_id) === String(formDefaults.value.empleado_id) &&
      String(r.periodo_mes) === String(formDefaults.value.periodo_mes) &&
      String(r.periodo_anio) === String(formDefaults.value.periodo_anio)
    );
    if (idx > -1) {
      originalRows.value[idx] = {
        ...originalRows.value[idx],
        ...record, // si tu Form emite el registro completo
        id: record.id ?? record.pago_id ?? originalRows.value[idx].id,
        total: record.total ?? originalRows.value[idx].total,
        id: newId,
        total: newTotal,
        tipo_estado_id: idCompletado,
        tipo_estado_nombre: 'Completado',
      };
    } else {
      originalRows.value.unshift({
        ...record,
        id: newId,
        total: newTotal,
        tipo_estado_id: idCompletado,
        tipo_estado_nombre: 'Completado',
      });
    }
    showSuccess('Aprobado', 'Pago registrado como Completado');
  } else {
    // edit
    const idx = originalRows.value.findIndex(r => r.id === record.id);
    if (idx > -1) originalRows.value[idx] = { ...originalRows.value[idx], ...record };
    showSuccess('Actualizado', 'Pago modificado correctamente');
  }
  close();
}

// ==== Cancelar → Pendiente ====
async function cancelPayment(row) {
  const ok = await ask('¿Revertir a Pendiente?', `Empleado: ${row.nombre} ${row.apellido}`, 'Sí, revertir');
  if (!ok) return;

  try {
    busy.value = true;
    const { data } = await axios.put(`${baseUrl}/${row.id}`, { tipo_estado_id: idPendiente }, {
      headers: { 'X-CSRF-TOKEN': csrf }
    });
    busy.value = false;
    if (data?.success) {
      const idx = originalRows.value.findIndex(r => r.id === row.id);
      if (idx > -1) {
        originalRows.value[idx] = {
          ...originalRows.value[idx],
          tipo_estado_id: idPendiente,
          tipo_estado_nombre: 'Pendiente',
          aprobado_id: null,
          aprobado_en: null,
        };
      }
      showSuccess('Revertido', 'El pago volvió a Pendiente');
    } else {
      showError('Atención', data?.message || 'No se pudo revertir');
    }
  } catch (e) {
    busy.value = false;
    showError('Error', 'No se pudo revertir a Pendiente');
  }
}

// Alerts
async function ask(title, text, confirmText = 'Confirmar') {
  const res = await Swal.fire({
    title, text, icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: confirmText,
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });
  return res.isConfirmed;
}
function showSuccess(title, text, timer = 2200) {
  Swal.fire({ icon: 'success', title, text, timer, timerProgressBar: true, showConfirmButton: timer === false });
}
function showError(title, text) {
  Swal.fire({ icon: 'error', title, text });
}

// Lifecycle
onMounted(async () => {
  await nextTick();
});

function estadoBadge(estado) {
  return estado === 'Completado' ? 'bg-success'
       : estado === 'Pendiente'  ? 'bg-warning text-dark'
       : estado === 'Cancelado'  ? 'bg-danger'
       : 'bg-secondary';
}
</script>

<style scoped>
.badge { padding: 0.3rem 0.6rem; border-radius: 0.3rem; font-size: 0.875rem; font-weight: 500; display: inline-block; }
/* .slide-enter-active, .slide-leave-active { transition: transform 0.25s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(-8px); }
.backdrop-enter-active, .backdrop-leave-active { transition: opacity 0.15s ease; }
.backdrop-enter-from, .backdrop-leave-to { opacity: 0; } */
</style>
