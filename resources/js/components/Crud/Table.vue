<template>
  <h4>{{ entityTitle }}</h4>

  <form class="row g-2 mb-3" method="GET">
    <template v-for="c in columns" :key="c.field">
      <div v-if="c.filterable" class="col-auto">
        <label class="form-label">{{ c.label }}</label>

        <select
          v-if="c.filterType==='select' && c.filterOptions"
          :name="buildName(c.field)"
          class="form-select"
          v-model="localFilters[c.field]"
        >
          <option value="">— Todos —</option>
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

  <a
    v-if="abilities.create"
    :href="createRoute"
    class="btn btn-sm btn-primary mb-2"
  >
    Nuevo
  </a>

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
          <a
            v-if="abilities.update"
            :href="editRoute(row.id)"
            class="btn btn-sm btn-warning"
          >
            ✎
          </a>

          <button
            v-if="abilities.delete"
            class="btn btn-sm btn-danger"
            @click="deleteRow(row)"
          >
            🗑
          </button>
        </td>
      </tr>
    </tbody>
  </table>

</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import DataTable from 'datatables.net'; 

const props = defineProps(['data','columns','abilities','filters']);

const rows = ref([...props.data.data]);
const localFilters = ref({ ...props.filters });

let dt = null;

onMounted(async () => {
  await nextTick();
  dt = new DataTable('#dataTable', {
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
    },
    paging: true,
    searching: true,
    ordering: true
  });
});

function buildName (field) {
  const parts = field.split('.');
  let name = parts.shift();
  parts.forEach(p => (name += `[${p}]`));
  
  return name;
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