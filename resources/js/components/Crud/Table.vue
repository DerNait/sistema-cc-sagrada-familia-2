<template>
  <div class="crud-container">
    <div class="m-3 d-flex justify-content-between align-items-center">
      <h4>{{ entityTitle }}</h4>

      <div class="d-flex justify-content-evenly gap-2">
        <a
          v-if="abilities.create"
          :href="createRoute"
          class="btn btn-primary"
        >
          <i class="fa-solid fa-plus"></i>
          Agregar {{ modelSingularName(entityTitle) }}
        </a>
        <button
          class="btn btn-info"
        >
          <i class="fa-solid fa-download"></i>
        </button>
      </div>
    </div>
  
    <form id="filters" class="d-flex g-2" method="GET">
      <template v-for="c in columns" :key="c.field">
        <div v-if="c.filterable" class="col-auto">
  
          <select
            v-if="c.filterType==='select' && c.filterOptions"
            :name="buildName(c.field)"
            class="form-select"
            v-model="localFilters[c.field]"
          >
            <option value="">{{ c.label }}</option>
            <option v-for="(label,val) in c.filterOptions" :key="val" :value="val">
              {{ label }}
            </option>
          </select>
  
          <input
            v-else
            :type="inputType(c.filterType)"
            :name="buildName(c.field)"
            class="form-control"
            v-model="localFilters[c.field]"
          />
        </div>
      </template>
  
      <div v-if="hasFilters()" class="col-auto align-self-end">
        <button type="submit" class="btn btn-primary">Filtrar</button>
      </div>
      <div v-if="hasFilters()" class="col-auto align-self-end">
        <a :href="indexRoute" class="btn btn-outline-secondary">Limpiar</a>
      </div>
    </form>
  
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
              <a
                v-if="abilities.update"
                :href="editRoute(row.id)"
                class="btn btn-sm btn-outline-primary"
              >
                <i class="fa-regular fa-pen-to-square"></i>
              </a>
    
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
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import DataTable from 'datatables.net-bs5';

const props = defineProps(['data','columns','abilities','filters']);

const rows = ref([...props.data.data]);
const localFilters = ref({ ...props.filters });

let dt = null;

onMounted(async () => {
  await nextTick();

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
  }, 100);
});

function buildName (field) {
  const parts = field.split('.');
  let name = parts.shift();
  parts.forEach(p => (name += `[${p}]`));
  
  return name;
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

function hasFilters () {
  return Object.values(props.columns).some(c => c.filterable);
}

const baseUrl = window.location.pathname.split('?')[0];

const segments = baseUrl.split('/');
const segment = segments.filter(Boolean).pop(); 

const indexRoute  = baseUrl;
const createRoute = `${baseUrl}/crear`;

function editRoute (id)  { 
    return `${baseUrl}/${id}/editar`;
}

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
})
</script>