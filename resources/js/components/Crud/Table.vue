<template>
  <div class="crud-container">
    <div class="m-3 d-flex justify-content-between align-items-center">
      <h4 class="fw-semibold">{{ entityTitle }}</h4>

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
          @click="exportData"
        >
          <i class="fa-solid fa-download"></i>
        </button>
      </div>
    </div>

    <div class="filters-container d-flex px-3 py-4 d-flex justify-content-between align-items-center">
      <div id="filters" class="d-flex flex-wrap g-2">
        <template v-for="c in columns" :key="c.field">
          <div v-if="c.filterable" class="col-auto">
            <select
              v-if="c.filterType==='select' && c.filterOptions"
              v-model="localFilters[c.field]"
              class="form-select"
            >
              <option value="">{{ c.label }}</option>
              <option v-for="(label,val) in c.filterOptions" :key="val" :value="val">
                {{ label }}
              </option>
            </select>
  
            <input
              v-else
              :type="inputType(c.filterType)"
              v-model="localFilters[c.field]"
              class="form-control"
              :placeholder="c.label"
            />
          </div>
        </template>
  
        <div v-if="hasActiveFilters" class="align-self-end ms-2">
          <button class="btn btn-outline-secondary" @click="clearFilters">
            Limpiar
          </button>
        </div>
      </div>
  
      <div style="max-width: 270px; flex:1">
        <SearchBar v-model="globalSearch" />
      </div>
    </div>
  

    <SortableTable
      :columns="columns"
      :rows="rows"
    >
      <template #row-actions="{ row }">
        <div class="d-flex justify-content-evenly">
          <button
            v-if="abilities.read"
            class="btn btn-sm btn-outline-secondary"
            @click="openShow(row)"
          >
            <i class="fa-regular fa-eye" />
          </button>
          <button
            v-if="abilities.update"
            class="btn btn-sm btn-outline-primary"
            @click="openEdit(row)"
          >
            <i class="fa-regular fa-pen-to-square" />
          </button>
          <button
            v-if="abilities.delete"
            class="btn btn-sm btn-outline-danger"
            @click="deleteRow(row)"
          >
            <i class="fa-solid fa-trash" />
          </button>
        </div>
      </template>

      <!-- ejemplo de celda personalizada -->
      <!--
      <template #cell-status="{ value }">
        <span :class="value ? 'badge bg-success' : 'badge bg-danger'">
          {{ value ? 'Activo' : 'Inactivo' }}
        </span>
      </template>
      -->
    </SortableTable>
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
          :readonly="formMode === 'show'"
          @saved="onSaved"
          @cancel="close" />
      </transition>
    </div>
  </transition>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue';
import SortableTable from '../SortableTable.vue';
import SearchBar from '../SearchBar.vue';
import Form from './Form.vue';

const showForm   = ref(false);
const editingRow = ref(null);       // null = create
const formAction = ref('');
const formMode = ref('create');

const props = defineProps(['data','columns','abilities','filters']);

const originalRows = ref([...props.data]);
const rows         = ref([...originalRows.value]);

const globalSearch = ref('');

const localFilters = reactive(
  Object.fromEntries(
    Object.values(props.columns)
      .filter(c => c.filterable)
      .map(c => [c.field, ''])
  )
);

function openCreate() {
  formMode.value  = 'create';
  editingRow.value = null;
  formAction.value = baseUrl;      // POST /entidad
  open();
}

function openShow(row) {
  formMode.value = 'show';
  loadLatest(row.id).then(record => {
    editingRow.value = record;
    formAction.value = '';
    open();
  });
}

async function loadLatest(id) {
  try {
    const { data } = await axios.get(`${baseUrl}/${id}`);
    return data;
  } catch (e) {
    console.error(e);
    return rows.value.find(r => r.id === id);
  }
}

function openEdit(row) {
  formMode.value  = 'edit';
  editingRow.value = { ...row };
  formAction.value = `${baseUrl}/${row.id}`; // PUT /entidad/{id}
  open();
}
function open()  { showForm.value = true; document.body.style.overflow = 'hidden'; }
function close() { showForm.value = false; document.body.style.overflow = '';      }

function onSaved (record) {
  const idx = rows.value.findIndex(r => r.id === record.id);

  if (idx > -1) {
    // UPDATE
    rows.value[idx]         = record;
    originalRows.value[idx] = record;
  } else {
    // CREATE
    rows.value.unshift(record);
    originalRows.value.unshift(record);
  }
  close();
}
onMounted(async () => {
  await nextTick();
});

watch([localFilters, globalSearch], applyFilters, { deep: true });

function applyFilters () {
  const term = globalSearch.value.trim().toLowerCase();

  rows.value = originalRows.value.filter(row => {
    /* 1 ) búsqueda global */
    const matchesGlobal = !term || Object.values(props.columns).some(col => {
      const cell = getValue(row, col.field);
      return String(cell ?? '').toLowerCase().includes(term);
    });

    /* 2 ) filtros por columna */
    const matchesColumnFilters = Object.entries(localFilters).every(([field,val]) => {
      if (!val) return true;

      const col  = props.columns[field];
      const cell = getValue(row, field);

      switch (col.filterType) {
        case 'numeric': return Number(cell) === Number(val);
        case 'date':    return (cell ?? '').slice(0,10) === val;
        case 'select':  return String(cell) === String(val);
        default:        return String(cell ?? '').toLowerCase().includes(String(val).toLowerCase());
      }
    });

    return matchesGlobal && matchesColumnFilters;
  });
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
        originalRows.value.splice(idx, 1);
      }
    })
    .catch(err => console.log(err.message));
}

const entityTitle = computed(() => {
  return segment.charAt(0).toUpperCase() + segment.slice(1);
});

function clearFilters () {
  Object.keys(localFilters).forEach(k => (localFilters[k] = ''));
}

const hasActiveFilters = computed(() =>
  Object.values(localFilters).some(v => v !== '')
);

//Funcion para poder exportar la data
function exportData() {
  axios.get(`${baseUrl}/export`, { responseType: 'blob' })
    .then(response => {
      // Crear un enlace temporal para descargar el archivo
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      
      // Obtener el nombre del archivo del header o generarlo
      const contentDisposition = response.headers['content-disposition'];
      let fileName = 'export.csv';
      if (contentDisposition) {
        const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
        if (fileNameMatch && fileNameMatch[1]) {
          fileName = fileNameMatch[1];
        }
      }
      
      link.setAttribute('download', fileName);
      document.body.appendChild(link);
      link.click();
      
      // Limpiar
      link.remove();
      window.URL.revokeObjectURL(url);
    })
    .catch(error => {
      console.error('Error al exportar:', error);
      alert('Error al exportar los datos');
    });
}
</script>