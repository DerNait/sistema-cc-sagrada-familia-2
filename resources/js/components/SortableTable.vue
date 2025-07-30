<template>
  <div>
    <!-- ========= TABLA ========= -->
    <div class="table-scroll mb-0">
        <table class="table table-bordered mb-0">
          <thead>
            <tr>
              <template v-for="c in colArray" :key="c.field">
                <th
                  v-if="c.visible"
                  class="sortable"
                  @click="toggleSort(c.field)"
                >
                  {{ c.label }}
                  <i
                    v-if="sortField === c.field"
                    :class="sortDir === 'asc'
                      ? 'fa-solid fa-sort-up ms-1'
                      : 'fa-solid fa-sort-down ms-1'" />
                  <i v-else class="fa-solid fa-sort ms-1 text-muted" />
                </th>
              </template>
              <th v-if="hasRowActionsSlot"></th>
            </tr>
          </thead>
    
          <tbody>
            <tr v-for="row in paginatedRows" :key="row.id">
              <template v-for="c in colArray" :key="c.field">
                <td v-if="c.visible">
                  <slot
                    v-if="$slots[`cell-${c.field}`]"
                    :name="`cell-${c.field}`"
                    :row="row"
                    :value="getValue(row, c.field)"
                  />
                  <template v-else>
                    <template v-if="c.type === 'relation' && c.options">
                      {{ c.options[row[c.field]] ?? '' }}
                    </template>
                    <template v-else>
                      {{ getValue(row, c.field) }}
                    </template>
                  </template>
                </td>
              </template>
    
              <td v-if="hasRowActionsSlot">
                <slot name="row-actions" :row="row" />
              </td>
            </tr>
          </tbody>
        </table>
    </div>

    <!-- ========= BARRA INFERIOR ========= -->
    <div class="d-flex justify-content-between align-items-center mt-2 px-3">
      <div class="d-flex align-items-center gap-2">
        <label class="mb-0 fw-semibold small">Mostrar</label>
        <select
          v-model.number="pageLength"
          class="form-select form-select-sm w-auto"
        >
          <option
            v-for="opt in pageLengthOptions"
            :key="opt"
            :value="opt">
            {{ opt === -1 ? 'Todos' : opt }}
          </option>
        </select>
        <span class="small">registros</span>
      </div>

      <nav v-if="pageCount > 1">
        <ul class="pagination pagination-sm mb-0">
          <li :class="['page-item', { disabled: currentPage === 1 }]">
            <a class="page-link" href="#" @click.prevent="goTo(currentPage-1)">
              «
            </a>
          </li>

          <li
            v-for="n in pageNumbers"
            :key="n"
            :class="['page-item', { active: n === currentPage }]"
          >
            <a class="page-link" href="#" @click.prevent="goTo(n)">{{ n }}</a>
          </li>

          <li :class="['page-item', { disabled: currentPage === pageCount }]">
            <a class="page-link" href="#" @click.prevent="goTo(currentPage+1)">
              »
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, useSlots, watch } from 'vue';

/* ---------- props ---------- */
const props = defineProps({
  columns:  { type: Object, required: true },
  rows:     { type: Array,  required: true },
  /* opcional: tamaños de página */
  pageLengths: { type: Array, default: () => [1, 25, 50, 100, -1] }
});

/* ---------- slots ---------- */
const slots = useSlots();
const hasRowActionsSlot = computed(() => !!slots['row-actions']);

/* ---------- ordenamiento ---------- */
const colArray  = computed(() => Object.values(props.columns));
const sortField = ref('');
const sortDir   = ref('asc');

function toggleSort (field) {
  sortField.value === field
    ? (sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc')
    : (sortField.value = field, sortDir.value = 'asc');
}

function getValue (obj, path) {
  return path.split('.').reduce((o, p) => (o ? o[p] : null), obj);
}

const sortedRows = computed(() => {
  if (!sortField.value) return props.rows;

  const field = sortField.value;
  const col   = props.columns[field];

  return [...props.rows].sort((a, b) => {
    let x = getValue(a, field);
    let y = getValue(b, field);

    if (col?.filterType === 'numeric') { x = Number(x); y = Number(y); }
    else if (col?.filterType === 'date') { x = new Date(x); y = new Date(y); }
    else { x = String(x).toLowerCase(); y = String(y).toLowerCase(); }

    return sortDir.value === 'asc' ? (x > y) - (x < y) : (y > x) - (y < x);
  });
});

/* ---------- paginación ---------- */
const pageLengthOptions = computed(() => props.pageLengths);
const pageLength = ref(pageLengthOptions.value[0]); // -1 => "Todos"
const currentPage = ref(1);

const pageCount = computed(() =>
  pageLength.value === -1
    ? 1
    : Math.max(1, Math.ceil(sortedRows.value.length / pageLength.value))
);

const paginatedRows = computed(() => {
  if (pageLength.value === -1) return sortedRows.value;
  const start = (currentPage.value - 1) * pageLength.value;
  return sortedRows.value.slice(start, start + pageLength.value);
});

/* reiniciar página si cambia longitud o cantidad de filas */
watch([pageLength, sortedRows], () => {
  if (currentPage.value > pageCount.value) currentPage.value = pageCount.value;
});

/* números a mostrar (máx 5 alrededor) */
const pageNumbers = computed(() => {
  const total = pageCount.value;
  const cur   = currentPage.value;
  const delta = 2;             // páginas a cada lado

  const range = [];
  for (let i = Math.max(1, cur - delta); i <= Math.min(total, cur + delta); i++) {
    range.push(i);
  }
  // forzar inicio / fin si están lejos
  if (range[0] !== 1) range.unshift(1);
  if (range[range.length - 1] !== total) range.push(total);

  return range;
});

function goTo(n) {
  if (n < 1 || n > pageCount.value) return;
  currentPage.value = n;
}
</script>

<style scoped>
.sortable { cursor: pointer; user-select: none; }

/* ocultar foco azul en <a> de paginación (opcional) */
.page-link:focus { box-shadow: none; }

.table-scroll {
  overflow-x: auto;                 /* crea el scroll horizontal */
  -webkit-overflow-scrolling: touch;/* deslizamiento suave en móviles */
}

.table-scroll table {
  min-width: max-content;           /* ancho mínimo = ancho total de columnas */
}
</style>
