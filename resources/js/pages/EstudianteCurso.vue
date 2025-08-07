<template>
  <div class="center-container">
    <h3 class="p-3 fw-bold m-0">{{ curso_name }}</h3>
    <SortableTable 
      :columns="columns"
      :rows="actividades"
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
import SortableTable from '../components/SortableTable.vue';
import { Chart } from 'highcharts-vue'

const props = defineProps(['curso_name', 'actividades', 'cursos', 
  'total_calificado', 'total_del_curso', 'center_labels']);

const columns = {
  nombre: {
    label: 'Asignación',
    field: 'nombre',
    visible: true
  },
  fechas: {
    label: 'Fechas',
    field: 'fechas', // usamos slot
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
