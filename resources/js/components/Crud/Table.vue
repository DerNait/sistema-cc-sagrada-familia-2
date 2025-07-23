<template>
  <div class="crud-container">
    <div class="m-3 d-flex justify-content-between align-items-center">
      <h4>{{ entityTitle }}</h4>

      <div class="d-flex justify-content-evenly gap-2">
        <button
          v-if="abilities.create"
          class="btn btn-primary" 
          @click="openCreate()"
        >
          <i class="fa-solid fa-plus"></i>
          Agregar {{ modelSingularName(entityTitle) }}
        </button>
        <button
          class="btn btn-info"
        >
          <i class="fa-solid fa-download"></i>
        </button>
      </div>
    </div>
  
    <!-- filtros: ya no es <form>, sino un simple wrapper -->
    <div id="filters" class="d-flex flex-wrap g-2 mb-3">
      <template v-for="c in columns" :key="c.field">
        <div v-if="c.filterable" class="col-auto">
          <!-- SELECT -->
          <select
            v-if="c.filterType==='select' && c.filterOptions"
            v-model="localFilters[c.field]"
            class="form-select"
          >
            <!-- valor inicial = '' (equivale a “sin filtro”), etiqueta = c.label -->
            <option value="">{{ c.label }}</option>
            <option v-for="(label,val) in c.filterOptions" :key="val" :value="val">
              {{ label }}
            </option>
          </select>

          <!-- INPUT -->
          <input
            v-else
            :type="inputType(c.filterType)"
            v-model="localFilters[c.field]"
            class="form-control"
            :placeholder="c.label"
          />
        </div>
      </template>

      <!-- Botón limpiar -->
      <div v-if="hasActiveFilters" class="align-self-end">
        <button class="btn btn-outline-secondary" @click="clearFilters">
          Limpiar
        </button>
      </div>
    </div>

    <table id="dataTable" class="table table-bordered display">
      <thead>
        <tr>
          <template v-for="c in Object.values(columns)" :key="c.field">
              <th v-if="c.visible">{{ c.label }}</th>
          </template>
          <th></th>
        </tr>
      </thead>
  
      <tbody>
        <tr v-for="row in rows" :key="row.id" :data-id="row.id">
          <template v-for="c in Object.values(columns)" :key="c.field">
              <td v-if="c.visible">
              <template v-if="c.type==='relation' && c.options">
                  {{ c.options[row[c.field]] ?? '' }}
              </template>
              <template v-else>
                  {{ getValue(row,c.field) }}
              </template>
              </td>
          </template>
          <td>
            <div class="d-flex justify-content-evenly">
              <button
                v-if="abilities.update"
                class="btn btn-sm btn-outline-primary" 
                @click="openEdit(row)"
              >
                <i class="fa-regular fa-pen-to-square"></i>
              </button>
    
              <button
                v-if="abilities.delete"
                class="btn btn-sm btn-outline-danger"
                @click="deleteRow(row)"
              >
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <transition name="backdrop">
    <div v-if="showForm"
         class="crud-backdrop d-flex justify-content-start"
         @click.self="close">
      <transition name="slide">
        <Form
          v-if="showForm"
          class="crud-drawer"
          :item="editingRow"
          :columns="columns"
          :action="formAction"
          @saved="onSaved"
          @cancel="close" />
      </transition>
    </div>
  </transition>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue';
import Form from './Form.vue';
import DataTable from 'datatables.net-bs5';

const showForm   = ref(false);
const editingRow = ref(null);       // null = create
const formAction = ref('');

const props = defineProps(['data','columns','abilities','filters']);

const originalRows = ref([...props.data.data]);
const rows         = ref([...originalRows.value]);

const localFilters = reactive(
  Object.fromEntries(
    Object.values(props.columns)
      .filter(c => c.filterable)
      .map(c => [c.field, ''])
  )
);

function openCreate() {
  editingRow.value = null;
  formAction.value = `${baseUrl}/crear`;      // POST /entidad
  open();
}
function openEdit(row) {
  editingRow.value = { ...row };
  formAction.value = `${baseUrl}/${row.id}`; // PUT /entidad/{id}
  open();
}
function open()  { showForm.value = true; document.body.style.overflow = 'hidden'; }
function close() { showForm.value = false; document.body.style.overflow = '';      }

function onSaved(record) {
  const idx = rows.value.findIndex(r => r.id === record.id);
  if (idx > -1) rows.value[idx] = record;
  else rows.value.unshift(record);
  close();
}

let dt = null;

onMounted(async () => {
  await nextTick();

  initDataTable();
});

watch(rows, async () => {
  await nextTick();

  if (dt) {
    dt.clear();
    const newRows = Array.from(
      document.querySelectorAll('#dataTable tbody tr')
    );
    newRows.forEach(tr => dt.row.add(tr));

    dt.draw(false);
  }
});

watch(
  localFilters,
  () => applyFilters(),
  { deep: true }
);

function initDataTable() {
  dt = new DataTable('#dataTable', {
    language: {
      url: '/lang/datatables/es-ES.json'
    },
    paging: true,
    searching: true,
    ordering: true,
    layout: {
      topStart: null,
      topEnd: 'search',
      bottomStart: 'pageLength',
      bottomEnd: 'paging'
    }
  });

  setTimeout(() => {
    placeFilters();
  }, 100);
}

function placeFilters() {
  const layoutTableEl = document.querySelector('.dt-layout-table');

  if (layoutTableEl) {
    const parent = layoutTableEl.closest('.row.mt-2');
    if (parent) {
      parent.classList.remove('mt-2');
    }
  }

  const wrapper = document.getElementById('dataTable_wrapper');

  if (wrapper) {
    const firstRow = wrapper.querySelector('.row.mt-2.justify-content-between');

    if (firstRow) {
      firstRow.classList.remove('mt-2', 'row');
      firstRow.classList.add('py-3', 'px-2', 'bg-gray-soft', 'd-flex', 'align-items-center');
      
      const filters = document.getElementById('filters');
      if (filters) {
        firstRow.prepend(filters);
      }
    }
    
    const matchingRows = wrapper.querySelectorAll('.row.mt-2.justify-content-between');
    console.log(matchingRows);

    if (matchingRows.length > 0) {
      const lastRow = matchingRows[matchingRows.length - 1];
      console.log(lastRow);

      lastRow.classList.remove('mt-2', 'row');
      lastRow.classList.add('mt-1', 'px-2', 'd-flex', 'align-items-center');
    }
  }
}

function modelSingularName(pluralName) {
  if (pluralName.endsWith('s')) {
    return pluralName.slice(0, -1);
  }
  return pluralName;
}

function inputType (t) {
  if (t === 'numeric') return 'number';
  if (t === 'date') return 'date';
  return 'text';
}

function getValue (obj, path) {
  return path.split('.').reduce((o, p) => (o ? o[p] : null), obj);
}

const baseUrl = window.location.pathname.split('?')[0];

const segments = baseUrl.split('/');
const segment = segments.filter(Boolean).pop(); 

function deleteRow (row) {
  if (!confirm('¿Eliminar?')) return;

  axios
    .delete(`${baseUrl}/${row.id}`)
    .then(() => {
      const idx = rows.value.findIndex(r => r.id === row.id);
      
      if (idx > -1) {
          rows.value.splice(idx, 1);
          dt?.row(`tr[data-id="${row.id}"]`).remove().draw(false);
      }
    })
    .catch(err => console.log(err.message));
}

const entityTitle = computed(() => {
  return segment.charAt(0).toUpperCase() + segment.slice(1);
});

function applyFilters () {
  rows.value = originalRows.value.filter(row =>
    Object.entries(localFilters).every(([field, val]) => {
      if (!val) return true;

      const col  = props.columns[field];
      const cell = getValue(row, field);

      switch (col.filterType) {
        case 'numeric': return Number(cell) === Number(val);
        case 'date':    return (cell ?? '').slice(0, 10) === val;
        case 'select':  return String(cell) === String(val);
        default:        return String(cell ?? '')
                           .toLowerCase()
                           .includes(String(val).toLowerCase());
      }
    })
  );
}

function clearFilters () {
  Object.keys(localFilters).forEach(k => (localFilters[k] = ''));
  dt.search('').draw(false);              // limpia búsqueda global, por si acaso
}

const hasActiveFilters = computed(() =>
  Object.values(localFilters).some(v => v !== '')
);
</script>