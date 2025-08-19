<template>
  <div class="center-container">
    <h3 class="p-3 fw-bold m-0">{{ curso_name }}</h3>

    <!-- Filtros -->
    <div class="filters-container px-3 py-4 d-flex justify-content-evenly align-items-end gap-2">
      <!-- Buscar/seleccionar actividad por nombre -->
       <div>
          <p class="fw-bold mb-1 fs-5 ms-1">Tarea</p>
          <Filtros
            v-model="filtros.actividad_id"
            :options="actividadOptions"
            value-key="id"
            label-key="nombre"
            placeholder="Buscar actividad…"
          />
       </div>

      <div>
        <p class="fw-bold mb-1 fs-5 ms-1">Desde</p>
        <DateFiltro v-model="filtros.fecha_inicio" placeholder="Fecha inicio…" />
      </div>

      <div>
        <p class="fw-bold mb-1 fs-5 ms-1">Hasta</p>
        <DateFiltro v-model="filtros.fecha_fin" placeholder="Fecha fin…" />
      </div>

      <button class="btn btn-outline-secondary ms-2" @click="limpiarFiltros">
        Limpiar
      </button>
    </div>
     
    <SortableTable 
      :columns="columns"
      :rows="actividadesFiltradas"
    >
      <!-- Columna personalizada para la Nota -->
      <template #cell-nota="{ row }">
        <div class="nota-cell">
          {{ row.nota }} / {{ row.asignacion.total }}  
        </div>
      </template>
      
      <!-- Columna personalizada para Fechas -->
      <template #cell-fechas="{ row }">
        <div>
          Del: <span class="text-primary" v-if="row.fecha_inicio">{{ row.fecha_inicio }}</span><span v-else>-</span>  <br>
          Al: <span class="text-primary" v-if="row.fecha_fin">{{ row.fecha_fin }}</span><span v-else>-</span> 
        </div>
      </template>
      
      <!-- Columna personalizada para Tarea -->
      <template #cell-nombre="{ row }">
        <div class="fw-bold">
          {{ row.asignacion.nombre }}
        </div>
      </template>
    </SortableTable>

    <!-- Gráficas -->
    <div class="d-flex justify-content-center align-items-center charts-wrapper" style="padding: 5rem 10rem;">
      <div class="chart-container">
        <Chart 
          v-if="total_calificado"
          :options="total_calificado"
        />
        <span v-if="center_labels" class="chart-center-text mt-3">{{ center_labels.total_calificado }}</span>
      </div>
      <div class="chart-container">
        <Chart 
          v-if="total_del_curso"
          :options="total_del_curso"
        />
        <span v-if="center_labels" class="chart-center-text mt-3">{{ center_labels.total_del_curso }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import SortableTable from '../components/SortableTable.vue';
import Filtros from '../components/Filtros.vue';
import DateFiltro from '../components/DateFiltro.vue';
import { Chart } from 'highcharts-vue';

const props = defineProps(['curso_name', 'actividades', 'cursos', 
  'total_calificado', 'total_del_curso', 'center_labels']);

const filtros = ref({
  actividad_id: null,
  fecha_inicio: '',
  fecha_fin: ''
});

const actividadOptions = computed(() => {
  return (props.actividades || []).map(a => ({
    id: a.id,
    nombre: a.asignacion?.nombre ?? a.nombre ?? `Actividad #${a.id}`
  }));
});

const actividadesFiltradas = computed(() => {
  const selId  = filtros.value.actividad_id;
  const desde  = filtros.value.fecha_inicio;
  const hasta  = filtros.value.fecha_fin;

  return (props.actividades || []).filter(a => {
    // 1) por actividad seleccionada (si hay)
    const byActividad = !selId || a.id === selId;

    // 2) por rango de fechas (inclusivo)
    const fi = a.fecha_inicio ? String(a.fecha_inicio).slice(0,10) : null;
    const ff = a.fecha_fin    ? String(a.fecha_fin).slice(0,10)   : null;

    const inDesde = !desde || (fi && fi >= desde);
    const inHasta = !hasta || (ff && ff <= hasta);

    return byActividad && inDesde && inHasta;
  });
});

function limpiarFiltros () {
  filtros.value.actividad_id = null;
  filtros.value.fecha_inicio = '';
  filtros.value.fecha_fin    = '';
}

const columns = {
  nombre: {
    label: 'Asignación',
    field: 'nombre',
    visible: true
  },
  fechas: {
    label: 'Fechas',
    field: 'fechas',
    visible: true
  },
  comentario: {
    label: 'Comentarios',
    field: 'comentario',
    visible: true
  },
  nota: {
    label: 'Nota Obtenida',
    field: 'nota',
    visible: true
  }
};
</script>

<style scoped>
.nota-cell {
  font-size: 1.2rem;
  text-align: center;
  vertical-align: middle;
}

.charts-wrapper {
  gap: 2rem;
  width: 100%;
}

.chart-container {
  flex: 1;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  min-width: 0;
}

.chart-center-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 2.5rem;
  color: black;
  pointer-events: none;
}
</style>
