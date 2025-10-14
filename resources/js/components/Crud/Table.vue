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
          v-if="abilities.export"
          class="btn btn-info"
          @click="exportData"
        >
          <i class="fa-solid fa-download"></i>
        </button>
      </div>
    </div>

    <div class="filters-container d-flex px-3 py-4 d-flex justify-content-between align-items-center">
      <div id="filters" class="d-flex flex-wrap gap-2">
        <template v-for="c in columns" :key="c.field">
          <div v-if="c.filterable" class="col-auto">
            <Filtros
              v-if="c.filterType==='select' && c.filterOptions"
              v-model="localFilters[c.field]"
              :options="Object.entries(c.filterOptions).map(([val,label]) => ({ id: val, nombre: label }))"
              :placeholder="c.label"
            />
  
            <input
              v-else
              :type="inputType(c.filterType)"
              v-model="localFilters[c.field]"
              class="form-control"
              :placeholder="c.label"
            />
          </div>
        </template>
  
        <div v-if="hasActiveFilters" class="align-self-end">
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
            v-for="act in (custom_actions || []).filter(a => !a.ability || abilities[a.ability])"
            :key="act.name"
            :class="['btn btn-sm', act.btnClass]"
            @click="onCustomClick(act, row)"
          >
            <i :class="['fa', act.icon]" />
          </button>

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
import Swal from 'sweetalert2';
import SortableTable from '../SortableTable.vue';
import SearchBar from '../SearchBar.vue';
import Filtros from '../Filtros.vue'
import Form from './Form.vue';

const showForm   = ref(false);
const editingRow = ref(null);
const formAction = ref('');
const formMode = ref('create');

const props = defineProps(['data','columns','abilities','filters','custom_actions']);

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
  formAction.value = baseUrl;
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

async function openEdit(row) {
    formMode.value = 'edit';
    editingRow.value = { ...row };
    formAction.value = `${baseUrl}/${row.id}`;
    open();
  }

function open()  { showForm.value = true; document.body.style.overflow = 'hidden'; }
function close() { showForm.value = false; document.body.style.overflow = '';      }

// ✅ SweetAlert: Confirmación
async function showConfirmation(title, text, confirmText = 'Confirmar') {
  return await Swal.fire({
    title: title,
    text: text,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: confirmText,
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });
}

// ✅ SweetAlert: Éxito
function showSuccess(title, text, timer = 3000) {
  Swal.fire({
    icon: 'success',
    title: title,
    text: text,
    timer: timer,
    timerProgressBar: true,
    showConfirmButton: timer === false
  });
}

// ✅ SweetAlert: Error
function showError(title, text) {
  Swal.fire({
    icon: 'error',
    title: title,
    text: text
  });
}

// ✅ Eliminar con SweetAlert
async function deleteRow(row) {
  const result = await showConfirmation(
    '¿Eliminar registro?',
    'Esta acción no se puede deshacer',
    'Sí, eliminar'
  );

  if (!result.isConfirmed) return;

  try {
    await axios.delete(`${baseUrl}/${row.id}`);
    const idx = rows.value.findIndex(r => r.id === row.id);
    if (idx > -1) {
      rows.value.splice(idx, 1);
      originalRows.value.splice(idx, 1);
    }
    showSuccess('Eliminado', 'El registro ha sido eliminado correctamente');
  } catch (error) {
    console.error(error);
    showError('Error', 'No se pudo eliminar el registro');
  }
}

async function onSaved(record) {
  if (formMode.value === 'create') {
    rows.value.unshift(record);
    originalRows.value.unshift(record);
    showSuccess('Guardado', 'Registro creado correctamente');
  } else {
    const idx = rows.value.findIndex(r => r.id === record.id);
    if (idx > -1) {
      rows.value[idx] = record;
      const idx2 = originalRows.value.findIndex(r => r.id === record.id);
      if (idx2 > -1) originalRows.value[idx2] = record;
    }
    showSuccess('Actualizado', 'Registro modificado correctamente');
  }
  close();
}

onMounted(async () => {
  await nextTick();

  const url = new URL(window.location.href);

  if (url.searchParams.get('create') && props.abilities?.create) {
    openCreate();
    url.searchParams.delete('create');
  } else if (url.searchParams.get('edit') && props.abilities?.update) {
    const id = url.searchParams.get('edit');
    const record = await loadLatest(id);
    if (record) {
      openEdit(record);
      url.searchParams.delete('edit');
    }
  } else if (url.searchParams.get('show') && props.abilities?.read) {
    const id = url.searchParams.get('show');
    const record = await loadLatest(id);
    if (record) {
      openShow(record);
      url.searchParams.delete('show');
    }
  }

  history.replaceState(null, '', url.pathname + (url.search ? '?' + url.search : '') + url.hash);
});

watch([localFilters, globalSearch], applyFilters, { deep: true });

function applyFilters () {
  const term = globalSearch.value.trim().toLowerCase();

  rows.value = originalRows.value.filter(row => {
    const matchesGlobal = !term || Object.values(props.columns).some(col => {
      const cellText = getDisplayValue(row, col).toLowerCase();
      return cellText.includes(term);
    });

    const matchesColumnFilters = Object.entries(localFilters).every(([field, val]) => {
      if (!val) return true;
      const col = props.columns[field];

      if (col.filterType === 'text') {
        const display = getDisplayValue(row, col).toLowerCase();
        return display.includes(String(val).toLowerCase());
      }
      if (col.filterType === 'select') {
        const raw = getValue(row, field);
        return String(raw) === String(val);
      }
      if (col.filterType === 'numeric') {
        const raw = getValue(row, field);
        return Number(raw) === Number(val);
      }
      if (col.filterType === 'date') {
        const raw = getValue(row, field);
        return String(raw ?? '').slice(0, 10) === val;
      }

      const display = getDisplayValue(row, col).toLowerCase();
      return display.includes(String(val).toLowerCase());
    });

    return matchesGlobal && matchesColumnFilters;
  });
}

function modelSingularName(pluralName) {
  const word = pluralName.trim();

  if (word.endsWith('es')) {
    return word.slice(0, -2);
  } else if (word.endsWith('s')) {
    return word.slice(0, -1);
  }
  return word;
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

const entityTitle = computed(() => {
  return segment.charAt(0).toUpperCase() + segment.slice(1);
});

function clearFilters () {
  Object.keys(localFilters).forEach(k => (localFilters[k] = ''));
}

const hasActiveFilters = computed(() =>
  Object.values(localFilters).some(v => v !== '')
);

function exportData() {
  axios.get(`${baseUrl}/export`, { responseType: 'blob' })
    .then(response => {
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
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
      link.remove();
      window.URL.revokeObjectURL(url);
    })
    .catch(error => {
      console.error('Error al exportar:', error);
      showError('Error', 'No se pudo exportar los datos');
    });
}

function resolveUrl(action, row) {
  return action.url.replace('__ID__', row.id);
}

function getDisplayValue(row, col) {
  const raw = getValue(row, col.field);
  if (col.options && raw != null && Object.prototype.hasOwnProperty.call(col.options, raw)) {
    return String(col.options[raw] ?? '');
  }
  if (col.field.includes('.')) {
    return String(raw ?? '');
  }
  if (col.type === 'date') {
    return String(raw ?? '').slice(0, 10);
  }
  if (col.type === 'datetime') {
    return String(raw ?? '').slice(0, 19).replace('T', ' ');
  }
  if (col.type === 'boolean') {
    return raw ? 'Sí' : 'No';
  }
  return String(raw ?? '');
}

function onCustomClick(action, row) {
  window.location.href = resolveUrl(action, row);
}
</script>