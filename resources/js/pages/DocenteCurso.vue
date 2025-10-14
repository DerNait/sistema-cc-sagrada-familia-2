<template>
  <div class="crud-container">
    <div class="m-3 d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <h3 class="p-3 fw-bold m-0">
          {{ curso?.nombre || 'Curso' }}
        </h3>
        <button
          v-if="!bulkEdit"
          class="btn btn-outline-primary"
          @click="editCourse()"
        >
          <i class="fa-solid fa-pen-to-square me-1"></i>
          Editar curso
        </button>
        <span v-if="bulkEdit" class="badge-editando text-primary fw-semibold">
          EDITANDO TODO
        </span>
      </div>

      <div class="d-flex justify-content-evenly gap-2">
        <button
          v-if="!bulkEdit"
          class="btn btn-primary"
          @click="toggleBulkEdit(true)"
        >
          <i class="fa-solid fa-pen-to-square me-1"></i>
          Editar todas las notas
        </button>

        <template v-else>
          <button
            class="btn btn-primary"
            :disabled="ui.globalBusy"
            @click="saveAllNotas()"
          >
            <i v-if="!ui.globalBusy" class="fa-solid fa-check me-1"></i>
            <i v-else class="fa-solid fa-spinner fa-spin me-1"></i>
            Guardar
          </button>

          <button
            class="btn btn-secondary"
            :disabled="ui.globalBusy"
            @click="cancelBulkEdit()"
          >
            <i class="fa-solid fa-xmark me-1"></i>
            Cancelar
          </button>
        </template>
      </div>
    </div>

    <div class="filters-container d-flex px-3 py-4 d-flex justify-content-between align-items-center">
      <!-- Grado (cambia grado -> refresca secciones, actividades, estudiantes/notas) -->
      <Filtros
        v-model="gradoId"
        :options="gradosLocal"
        label-key="nombre"
        placeholder="Selecciona un grado"
      />

      <!-- Sección (cambia seccion -> refresca estudiantes/notas) -->
      <Filtros
        v-model="seccionId"
        :options="seccionesLocal"
        label-key="seccion"
        placeholder="Selecciona una sección"
      />

      <!-- Estudiante (local) -->
      <Filtros
        v-model="filtroEstudianteId"
        :options="estudiantesLocal"
        label-key="nombre"
        placeholder="Filtrar por estudiante"
      />

      <!-- Actividad (local) -->
      <Filtros
        v-model="filtroActividadId"
        :options="actividadesLocal"
        label-key="nombre"
        placeholder="Filtrar por actividad"
      />
    </div>

    <div v-if="busyData" class="p-5 text-center text-muted">
      <i class="fa-solid fa-spinner fa-spin me-2"></i> Cargando…
    </div>

    <SortableTable
      v-else
      :key="columnsKey"
      :columns="columns"
      :rows="rowsLocal"
      :page-lengths="[10, 25, 50, 100, -1]"
    >
      <!-- Columna alumno -->
      <template #cell-estudiante="{ row }">
        <span class="text-secondary">{{ (row.estudiante).toUpperCase() }}</span>
      </template>

      <!-- Columnas dinámicas por actividad -->
      <template
        v-for="act in actsFuente"
        :key="`slot-${act.id}`"
        v-slot:[`cell-act_${act.id}`]="{ row, value }"
      >
        <div
          class="nota-cell"
          @mouseenter="setHover(cellKey(row.id, act.id), true)"
          @mouseleave="setHover(cellKey(row.id, act.id), false)"
        >
          <!-- Modo edición de nota -->
          <template v-if="bulkEdit || isEditing(cellKey(row.id, act.id))">
            <div class="nota-row">
              <input
                type="number"
                class="form-control form-control-sm nota-input"
                v-model.number="ui.noteDraft[cellKey(row.id, act.id)]"
                :disabled="ui.globalBusy || ui.busy[cellKey(row.id, act.id)]"
                step="0.01"
                @keyup.enter="saveNota(row.id, act.id)"
                @keyup.esc="cancelEdit(row.id, act.id)"
              />
              <div class="nota-right" v-if="!bulkEdit">
                <!-- Cancelar -->
                <button
                  class="btn btn-sm btn-link p-0 me-2"
                  :disabled="ui.globalBusy || ui.busy[cellKey(row.id, act.id)]"
                  @click="cancelEdit(row.id, act.id)"
                  title="Cancelar"
                >
                  <i class="fa-solid fa-xmark"></i>
                </button>
                <!-- Guardar -->
                <button
                  class="btn btn-sm btn-link p-0"
                  :disabled="ui.globalBusy || ui.busy[cellKey(row.id, act.id)]"
                  @click="saveNota(row.id, act.id)"
                  title="Guardar"
                >
                  <i class="fa-solid fa-check"></i>
                </button>
              </div>
            </div>
          </template>

          <!-- Modo normal -->
          <template v-else>
            <div class="nota-row">
              <span class="nota-text">
                {{ value?.nota ?? '—' }}
              </span>

              <!-- Contenedor de iconos pegado a la derecha -->
              <div class="nota-right">
                <button
                  class="btn btn-sm btn-link p-0 nota-comment-btn"
                  :class="{
                    'idle-hide': !value?.has_comentario && !isHovered(cellKey(row.id, act.id)) // oculto si no hay hover
                  }"
                  :disabled="ui.globalBusy || ui.busy[cellKey(row.id, act.id)]"
                  title="Comentario"
                  @click="openComment(row.id, act.id, value?.comentario)"
                >
                  <i
                    class="fa-regular fa-comment-dots"
                    :class="{
                      'text-primary': isHovered(cellKey(row.id, act.id)) && !!value?.has_comentario,
                      'text-dark': !!value?.has_comentario && !isHovered(cellKey(row.id, act.id))
                    }"
                  ></i>
                </button>

                <!-- Editar aparece SOLO en hover; al montarse, empuja al comentario -->
                <button
                  v-if="isHovered(cellKey(row.id, act.id))"
                  :disabled="ui.globalBusy || ui.busy[cellKey(row.id, act.id)]"
                  class="btn btn-sm btn-link p-0"
                  title="Editar nota"
                  @click="openEdit(row.id, act.id, value?.nota)"
                >
                  <i class="fa-regular fa-pen-to-square"></i>
                </button>
              </div>
            </div>
          </template>

          <!-- Overlay de comentario -->
          <div
            v-if="isCommenting(cellKey(row.id, act.id))"
            class="comment-popover shadow-sm"
          >
            <div class="card-body p-2">
              <textarea
                class="form-control form-control-sm"
                rows="2"
                v-model="ui.commentDraft[cellKey(row.id, act.id)]"
                placeholder="Escribe un comentario..."
              />
              <div class="d-flex justify-content-end mt-2 gap-2">
                <button
                  class="btn btn-sm btn-light"
                  :disabled="ui.busy[cellKey(row.id, act.id)]"
                  @click="closeComment(row.id, act.id)"
                >
                  Cancelar
                </button>
                <button
                  class="btn btn-sm btn-primary"
                  :disabled="ui.busy[cellKey(row.id, act.id)]"
                  @click="saveComentario(row.id, act.id)"
                >
                  Guardar
                </button>
              </div>
            </div>
          </div>
          <div v-if="ui.busy[cellKey(row.id, act.id)]" class="cell-loading">
            <i class="fa-solid fa-spinner fa-spin fa-lg"></i>
          </div>
        </div>
      </template>
    </SortableTable>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import axios from 'axios';
import SortableTable from '../components/SortableTable.vue';
import Filtros from '../components/Filtros.vue';

const props = defineProps({
  curso: { type: Object, required: true },
  grados: { type: Array, default: () => [] },
  selected_grado_id: { type: Number, default: null },
  secciones: { type: Array, default: () => [] },
  selected_seccion_id: { type: Number, default: null },
  estudiantes: { type: Array, default: () => [] },
  selected_estudiante_ids: { type: Array, default: () => [] },
  actividades: { type: Array, default: () => [] },
});

const busyData = ref(false);
const syncing  = ref(false);

const gradoId = ref(props.selected_grado_id);
const seccionId = ref(props.selected_seccion_id);
const estudiantesLocal = ref(props.estudiantes || []);
const actividadesLocal = ref(props.actividades || []);
const seccionesLocal = ref(props.secciones || []);
const gradosLocal = ref(props.grados || []);

const filtroEstudianteId = ref(null);
const filtroActividadId = ref(null);

const actsFuente = computed(() =>
  filtroActividadId.value
    ? actividadesLocal.value.filter(a => a.id === filtroActividadId.value)
    : actividadesLocal.value
);

watch(gradoId, async (nuevo, viejo) => {
  if (syncing.value) return;
  if (nuevo === viejo || !nuevo) return;
  await fetchData({ grado_id: nuevo, seccion_id: null });
});

watch(seccionId, async (nuevo, viejo) => {
  if (syncing.value) return;
  if (nuevo === viejo || !nuevo) return;
  await fetchData({ grado_id: gradoId.value, seccion_id: nuevo });
});

/* =========================
 * 1) Columnas
 * ========================= */

const columnsKey = computed(() => 'cols-' + actsFuente.value.map(a => a.id).join('-'));

const columns = computed(() => {
  const base = {
    estudiante: { label: 'Estudiante', field: 'estudiante', visible: true },
  };
  actsFuente.value.forEach((a) => {
    base[`act_${a.id}`] = {
      label: a.nombre,
      field: `act_${a.id}`,
      visible: true,
      filterType: 'numeric',
    };
  });
  return base;
});

/* =========================
 * 2) Construcción de filas y copia local editable
 * ========================= */
const builtRows = computed(() => {
  // index: actId -> estudiante_id -> {nota, comentario, has_comentario, seccion_estudiante_id}
  const notasIndex = {};
  for (const act of actividadesLocal.value) {
    const byStudent = {};
    for (const n of act.notas || []) {
      byStudent[n.estudiante_id] = {
        nota_id: n.id ?? null,
        nota: n.nota ?? null,
        comentario: n.comentario ?? null,
        has_comentario: !!n.has_comentario,
        seccion_estudiante_id: n.seccion_estudiante_id ?? null,
      };
    }
    notasIndex[act.id] = byStudent;
  }

  // filtro ligero por estudiante
  const estuFuente = filtroEstudianteId.value
    ? estudiantesLocal.value.filter(e => e.id === filtroEstudianteId.value)
    : estudiantesLocal.value;

  // filtro ligero por actividad (reduce columnas y datos)
  const acts = actsFuente.value;

  return (estuFuente || []).map((estu) => {
    const r = { id: estu.id, estudiante: estu.nombre };
    for (const act of acts) {
      r[`act_${act.id}`] = notasIndex[act.id]?.[estu.id] ?? {
        nota_id: null,
        nota: null,
        comentario: null,
        has_comentario: false,
        seccion_estudiante_id: null,
      };
    }
    return r;
  });
});

const rowsLocal = ref([]);
watch(builtRows, (v) => (rowsLocal.value = JSON.parse(JSON.stringify(v))), {
  immediate: true,
});

/* =========================
 * 3) UI por celda
 * ========================= */
const ui = reactive({
  edit: {},          // key -> true/false
  comment: {},       // key -> true/false
  noteDraft: {},     // key -> number
  commentDraft: {},  // key -> string
  busy: {},          // key -> loading
  globalBusy: false, // 🔵 deshabilita acciones en guardado masivo
});

const originalNotas = ref({});
const bulkEdit = ref(false);

const cellKey = (estId, actId) => `${estId}__${actId}`;
const isEditing = (key) => !!ui.edit[key];
const isCommenting = (key) => !!ui.comment[key];

const hovered = ref({});
const isHovered = (key) => !!hovered.value[key];

/* helpers para edición masiva */
function normalizeDraftToCompare(val) {
  // para comparar: ''/null/undefined => 0 (porque backend guarda 0)
  if (val === '' || val === null || typeof val === 'undefined') return 0;
  const n = Number(val);
  return Number.isFinite(n) ? n : 0;
}

function snapshotOriginalNotas() {
  const snap = {};
  for (const r of rowsLocal.value) {
    for (const a of actividadesLocal.value) {
      const key = cellKey(r.id, a.id);
      const cell = r[`act_${a.id}`] || {};
      snap[key] = normalizeDraftToCompare(cell.nota);
    }
  }
  originalNotas.value = snap;
}

function preloadAllDrafts() {
  for (const r of rowsLocal.value) {
    for (const a of actividadesLocal.value) {
      const key = cellKey(r.id, a.id);
      const cell = r[`act_${a.id}`] || {};
      ui.noteDraft[key] = (cell.nota ?? '').toString();
    }
  }
}

function toggleBulkEdit(state) {
  if (state) {
    preloadAllDrafts();
    snapshotOriginalNotas();
    bulkEdit.value = true;
  } else {
    bulkEdit.value = false;
    for (const k of Object.keys(ui.edit)) ui.edit[k] = false;
  }
}

function cancelBulkEdit() {
  // Revertir drafts a los valores del snapshot
  for (const r of rowsLocal.value) {
    for (const a of actividadesLocal.value) {
      const key = cellKey(r.id, a.id);
      const orig = originalNotas.value[key];
      ui.noteDraft[key] = (orig === 0 ? 0 : (orig ?? '')).toString();
    }
  }
  bulkEdit.value = false;
}

function getChangedCells() {
  const changes = [];
  for (const r of rowsLocal.value) {
    for (const a of actividadesLocal.value) {
      const key = cellKey(r.id, a.id);
      const rowCell = r[`act_${a.id}`] || {};
      const before = originalNotas.value[key]; // normalizado
      const draftNum = normalizeDraftToCompare(ui.noteDraft[key]);

      if (draftNum !== before) {
        changes.push({
          key,
          estId: r.id,
          actId: a.id,
          draftNum,
          cell: rowCell,
        });
      }
    }
  }
  return changes;
}

function saveAllNotas() {
  const urlBase = `/cursos/${props.curso.id}/notas`;
  const changes = getChangedCells();
  if (!changes.length) {
    // nada que guardar: salir limpio
    bulkEdit.value = false;
    return;
  }

  ui.globalBusy = true;

  const requests = changes.map(({ key, actId, draftNum, cell }) => {
    ui.busy[key] = true;

    const req = cell.nota_id
      ? axios.patch(`${urlBase}/${cell.nota_id}`, { nota: draftNum })
      : axios.post(urlBase, {
          actividad_id: actId,
          seccion_estudiante_id: cell.seccion_estudiante_id,
          nota: draftNum, // si null/'' -> backend lo deja en 0
        });

    return req.then((res) => {
        const data = res?.data || {};
        cell.nota_id = data.id ?? cell.nota_id ?? null;
        cell.nota = (typeof data.nota !== 'undefined') ? data.nota : 0;
        ui.busy[key] = false;
      })
      .catch((err) => {
        console.error('Error guardando nota (bulk):', err);
        ui.busy[key] = false;
        // opcional: podrías marcar error por celda si quieres
      });
  });

  Promise.all(requests)
    .then(() => {
      // refrescar snapshot con los nuevos valores
      snapshotOriginalNotas();
      bulkEdit.value = false;
      ui.globalBusy = false;
    })
    .catch(() => {
      // si alguna falló, igual desactivamos el busy global; mantén modo edición para reintentar
      ui.globalBusy = false;
    });
}


function setHover(key, state) {
  hovered.value[key] = state;
}

function openEdit(estId, actId, notaActual) {
  const key = cellKey(estId, actId);
  ui.edit[key] = true;
  ui.noteDraft[key] = (notaActual ?? '').toString();
}

function cancelEdit(estId, actId) {
  const key = cellKey(estId, actId);
  const row = rowsLocal.value.find((r) => r.id === estId);
  if (row) {
    const cell = row[`act_${actId}`] || {};
    ui.noteDraft[key] = (cell.nota ?? '').toString();
  }
  ui.edit[key] = false;
}

function openComment(estId, actId, comentarioActual) {
  const key = cellKey(estId, actId);
  ui.comment[key] = true;
  ui.commentDraft[key] = comentarioActual ?? '';
}

function closeComment(estId, actId) {
  const key = cellKey(estId, actId);
  ui.comment[key] = false;
  hovered.value[key] = false;
}

/* =========================
 * 4) Guardados con axios (sin await)
 * ========================= */
function saveNota(estId, actId) {
  const key = cellKey(estId, actId);
  const draftRaw = ui.noteDraft[key];
  const draftNum = draftRaw === '' || draftRaw === null || typeof draftRaw === 'undefined'
    ? null
    : Number(draftRaw);

  const row = rowsLocal.value.find((r) => r.id === estId);
  if (!row) return;
  const cell = row[`act_${actId}`] || {};

  const urlBase = `/cursos/${props.curso.id}/notas`;
  ui.busy[key] = true;

  const req = cell.nota_id
    ? axios.patch(`${urlBase}/${cell.nota_id}`, { nota: draftNum })
    : axios.post(urlBase, {
        actividad_id: actId,
        seccion_estudiante_id: cell.seccion_estudiante_id,
        nota: draftNum, // si va null, backend lo setea a 0
      });

  req.then((res) => {
      const data = res?.data || {};
      cell.nota_id = data.id ?? cell.nota_id ?? null;
      // usa lo que diga el backend; si no viene, cae a 0
      cell.nota = (typeof data.nota !== 'undefined') ? data.nota : 0;
      ui.edit[key] = false;
      if (!bulkEdit.value) ui.edit[key] = false;
      ui.busy[key] = false;
    })
    .catch((err) => {
      console.error('Error guardando nota:', err);
      ui.busy[key] = false;
    });
}

function saveComentario(estId, actId) {
  const key = cellKey(estId, actId);
  const draft = ui.commentDraft[key] ?? '';

  const row = rowsLocal.value.find((r) => r.id === estId);
  if (!row) return;
  const cell = row[`act_${actId}`] || {};

  const urlBase = `/cursos/${props.curso.id}/notas`;
  ui.busy[key] = true;

  const req = cell.nota_id
    ? axios.patch(`${urlBase}/${cell.nota_id}`, { comentario: draft })
    : axios.post(urlBase, {
        actividad_id: actId,
        seccion_estudiante_id: cell.seccion_estudiante_id,
        comentario: draft, // backend setea nota=0 si no viene
      });

  req.then((res) => {
      const data = res?.data || {};
      cell.nota_id = data.id ?? cell.nota_id ?? null;

      // 🔵 AQUI forzamos sincronizar la nota con lo que devolvió el backend
      //    Si no viniera por alguna razón, asumimos 0.
      if (typeof data.nota !== 'undefined') {
        cell.nota = data.nota;
      } else if (cell.nota == null) {
        cell.nota = 0;
      }

      cell.comentario = (typeof data.comentario !== 'undefined') ? data.comentario : draft;
      cell.has_comentario = !!(cell.comentario && String(cell.comentario).trim().length);

      ui.comment[key] = false;
      ui.busy[key] = false;
    })
    .catch((err) => {
      console.error('Error guardando comentario:', err);
      ui.busy[key] = false;
    });
}

async function fetchData(params = {}) {
  try {
    busyData.value = true;
    const { data } = await axios.get(`/cursos/${props.curso.id}/data`, { params });

    syncing.value = true;

    // refrescar combos
    gradosLocal.value    = data.grados || [];
    seccionesLocal.value = data.secciones || [];

    // Solo pisa la selección si NO la enviaste explícita
    if (params.grado_id == null)   gradoId.value   = data.selected_grado_id;
    if (params.seccion_id == null) seccionId.value = data.selected_seccion_id;

    // datasets
    estudiantesLocal.value = data.estudiantes || [];
    actividadesLocal.value = data.actividades || [];

    // limpiar filtros si ya no aplican
    if (filtroEstudianteId.value && !estudiantesLocal.value.some(e => e.id === filtroEstudianteId.value)) {
      filtroEstudianteId.value = null;
    }
    if (filtroActividadId.value && !actividadesLocal.value.some(a => a.id === filtroActividadId.value)) {
      filtroActividadId.value = null;
    }

    // si estabas en edición masiva, rearmar drafts
    if (bulkEdit.value) {
      preloadAllDrafts();
      snapshotOriginalNotas();
    }
  } catch (e) {
    console.error('Error cargando data:', e);
  } finally {
    syncing.value = false;
    busyData.value = false;
  }
}

function editCourse() {
  window.location.href = `/admin/cursos?edit=${props.curso.id}`;
}

</script>

<style scoped>
.nota-cell {
  position: relative;
  min-width: 120px;
}

/* fila dentro de la celda: nota a la izquierda, iconos a la derecha */
.nota-row {
  display: flex;
  align-items: center;
  gap: .5rem;
}

/* contenedor de iconos siempre a la derecha */
.nota-right {
  margin-left: auto;           /* empuja los iconos al extremo derecho */
  display: flex;
  align-items: center;
  gap: .5rem;
}

.nota-input {
  width: 14ch;
  min-width: 10ch;
  max-width: 14ch;
  flex: 0 0 auto;
}

/* comentario: oculto cuando no hay comentario y no hay hover */
.nota-comment-btn.idle-hide {
  opacity: 0;
  pointer-events: none;
  transition: opacity .15s ease-in-out;
}

/* cuando hay hover en la celda, aparece aunque no tenga comentario */
.nota-cell:hover .nota-comment-btn.idle-hide {
  opacity: 1;
  pointer-events: auto;
}

/* popover igual que antes */
.comment-popover {
  position: absolute;
  z-index: 5;
  top: 100%;
  right: 0;
  width: 260px;
  border-radius: 1rem;
  border: 0px solid transparent;
  background-color: rgba(255, 255, 255, 0.692);
  backdrop-filter: blur(1rem);
  -webkit-backdrop-filter: blur(1rem);
}

.cell-loading {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, .55);
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
  border-radius: .5rem;
  pointer-events: none; /* no bloquea el mouse; todo ya está :disabled */
}
</style>
