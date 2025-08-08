<template>
  <div class="center-container">
    <h3 class="p-3 fw-bold m-0">
      {{ curso?.nombre || 'Curso' }}
    </h3>

    <SortableTable
      :columns="columns"
      :rows="rowsLocal"
      :page-lengths="[10, 25, 50, 100, -1]"
    >
      <!-- Columna alumno -->
      <template #cell-estudiante="{ row }">
        <span class="text-secondary">{{ row.estudiante }}</span>
      </template>

      <!-- Columnas dinámicas por actividad -->
      <template
        v-for="act in actividades"
        :key="`slot-${act.id}`"
        v-slot:[`cell-act_${act.id}`]="{ row, value }"
      >
        <div class="nota-cell">
          <!-- Modo edición de nota -->
          <template v-if="isEditing(cellKey(row.id, act.id))">
            <input
              type="number"
              class="form-control form-control-sm w-75 d-inline-block"
              v-model.number="ui.noteDraft[cellKey(row.id, act.id)]"
              step="0.01"
              @keyup.enter="saveNota(row.id, act.id)"
            />
            <button
              class="btn btn-sm btn-link align-middle ms-1"
              :disabled="ui.busy[cellKey(row.id, act.id)]"
              @click="saveNota(row.id, act.id)"
              title="Guardar"
            >
              <i class="fa-solid fa-check"></i>
            </button>
          </template>

          <!-- Modo normal -->
          <template v-else>
            <span :class="value?.has_comentario ? 'text-primary' : ''">
              {{ value?.nota ?? '—' }}
            </span>

            <div class="nota-actions">
              <!-- Abrir comentario -->
              <button
                class="btn btn-sm btn-link p-0 me-2"
                title="Comentario"
                @click="openComment(row.id, act.id, value?.comentario)"
              >
                <i class="fa-regular fa-comment-dots"></i>
              </button>

              <!-- Editar nota -->
              <button
                class="btn btn-sm btn-link p-0"
                title="Editar nota"
                @click="openEdit(row.id, act.id, value?.nota)"
              >
                <i class="fa-regular fa-pen-to-square"></i>
              </button>
            </div>
          </template>

          <!-- Overlay de comentario -->
          <div
            v-if="isCommenting(cellKey(row.id, act.id))"
            class="comment-popover card shadow-sm"
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
        </div>
      </template>
    </SortableTable>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import axios from 'axios';
import SortableTable from '../components/SortableTable.vue';

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

/* =========================
 * 1) Columnas
 * ========================= */
const columns = computed(() => {
  const base = {
    estudiante: { label: 'Estudiante', field: 'estudiante', visible: true },
  };
  props.actividades.forEach((a) => {
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
  for (const act of props.actividades) {
    const byStudent = {};
    for (const n of act.notas || []) {
      byStudent[n.estudiante_id] = {
        nota: n.nota ?? null,
        comentario: n.comentario ?? null,
        has_comentario: !!n.has_comentario,
        seccion_estudiante_id: n.seccion_estudiante_id ?? null,
      };
    }
    notasIndex[act.id] = byStudent;
  }

  return (props.estudiantes || []).map((estu) => {
    const r = { id: estu.id, estudiante: estu.nombre };
    for (const act of props.actividades) {
      r[`act_${act.id}`] = notasIndex[act.id]?.[estu.id] ?? {
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
});
const cellKey = (estId, actId) => `${estId}__${actId}`;
const isEditing = (key) => !!ui.edit[key];
const isCommenting = (key) => !!ui.comment[key];

function openEdit(estId, actId, notaActual) {
  const key = cellKey(estId, actId);
  ui.edit[key] = true;
  ui.noteDraft[key] = (notaActual ?? '').toString();
}

function openComment(estId, actId, comentarioActual) {
  const key = cellKey(estId, actId);
  ui.comment[key] = true;
  ui.commentDraft[key] = comentarioActual ?? '';
}

function closeComment(estId, actId) {
  const key = cellKey(estId, actId);
  ui.comment[key] = false;
}

/* =========================
 * 4) Guardados con axios (sin await)
 * ========================= */
function saveNota(estId, actId) {
  const key = cellKey(estId, actId);
  const draft = ui.noteDraft[key];

  // Buscar celda en rowsLocal
  const row = rowsLocal.value.find((r) => r.id === estId);
  if (!row) return;
  const cell = row[`act_${actId}`] || {};
  const payload = {
    actividad_id: actId,
    estudiante_id: estId,
    seccion_estudiante_id: cell.seccion_estudiante_id, // si viene null, el backend puede inferirlo
    nota: draft === '' || draft === null ? null : Number(draft),
  };

  ui.busy[key] = true;

  axios
    .post('/docente/notas/upsert', payload)
    .then((res) => {
      // Actualiza UI con la respuesta o, si no hay, con el draft
      const newNota = res?.data?.nota ?? payload.nota ?? null;
      cell.nota = newNota;
      // al guardar nota no tocamos comentario
      ui.edit[key] = false;
      ui.busy[key] = false;
    })
    .catch((error) => {
      console.error('Error guardando nota:', error);
      ui.busy[key] = false;
    });
}

function saveComentario(estId, actId) {
  const key = cellKey(estId, actId);
  const draft = ui.commentDraft[key] ?? '';

  const row = rowsLocal.value.find((r) => r.id === estId);
  if (!row) return;
  const cell = row[`act_${actId}`] || {};
  const payload = {
    actividad_id: actId,
    estudiante_id: estId,
    seccion_estudiante_id: cell.seccion_estudiante_id,
    comentario: draft,
  };

  ui.busy[key] = true;

  axios
    .post('/docente/notas/comentario', payload)
    .then((res) => {
      const newComent = res?.data?.comentario ?? payload.comentario ?? null;
      cell.comentario = newComent;
      cell.has_comentario = !!(newComent && String(newComent).trim().length);
      ui.comment[key] = false;
      ui.busy[key] = false;
    })
    .catch((error) => {
      console.error('Error guardando comentario:', error);
      ui.busy[key] = false;
    });
}
</script>

<style scoped>
.nota-cell {
  position: relative;
  min-width: 120px;
  padding-right: 2rem; /* espacio para acciones */
}

.nota-actions {
  position: absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  opacity: 0;
  transition: opacity .15s ease-in-out;
  display: inline-flex;
  gap: .25rem;
}

.nota-cell:hover .nota-actions {
  opacity: 1;
}

.comment-popover {
  position: absolute;
  z-index: 5;
  top: 100%;
  right: 0;
  width: 260px;
}
</style>
