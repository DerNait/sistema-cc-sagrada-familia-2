<template>
  <div class="dashboard-container p-4">
    <!-- Mostrar KPIs solo si ya tenemos params -->
    <div
      v-if="params && params.totals"
      class="d-flex justify-content-end gap-5 mb-4"
      style="margin-top: 2rem; margin-right: 4rem;"
    >
      <Stat
        label="Usuarios"
        :value="params.totals.users"
        icon="fa-users"
      />

      <Stat
        label="Estudiantes"
        :value="params.totals.students"
        icon="fa-graduation-cap"
      />

      <Stat
        label="Empleados"
        :value="params.totals.employees"
        icon="fa-briefcase"
      />
    </div>

    <!-- Estado mientras carga o si no hay datos -->
    <div v-else class="text-center my-5">
      <p class="text-muted">Cargando datos del dashboard...</p>
    </div>

    <!-- Gráficos -->
    <div class="row g-4 mt-4 charts-row">
      <!-- Gráfico circular de estudiantes -->
      <div class="col-md-4 d-flex">
        <Chart
          title="Estudiantes"
          type="donut"
          :height="300"
          :series="studentsData.series"
          :show-labels="studentsData.labels"
          :colors="['#8FBC8F', '#2F4F4F']"
          donut-size="35%"
        />
      </div>

      <!-- Gráfico de barras de pagos -->
      <div class="col-md-4 d-flex">
        <Chart
          title="Pagos de estudiantes"
          type="bar"
          :height="300"
          :series="paymentsData.series"
          :categories="paymentsData.categories"
          :colors="['#8FBC8F', '#87CEEB', '#2F4F4F']"
        />
      </div>

      <!-- Gráfico de barras de becas horizontal -->
      <div class="col-md-4 d-flex">
        <Chart
          title="Beca de estudiantes"
          type="bar"
          :height="300"
          :series="scholarshipsData.series"
          :horizontal="true"
          :stacked="true"
          :colors="['#8FBC8F', '#2F4F4F']"
          hide-axis-labels
        />
      </div>
    </div>

    <!-- Segunda fila de gráficos -->
    <div class="row g-4 mt-1 charts-row">
      <!-- Gráfico de promedio de grados (ocupa 8 columnas para ser igual al ancho de los dos primeros de arriba) -->
      <div class="col-md-8 d-flex">
        <Chart
          title="Promedio de grados"
          type="bar"
          :height="300"
          :series="gradesData.series"
          :categories="gradesData.categories"
          :colors="['#8FBC8F']"
        />
      </div>

      <!-- Gráfico circular de estado del personal -->
      <div class="col-md-4 d-flex">
        <Chart
          title="Estado del personal"
          type="donut"
          :height="300"
          :series="staffStatusData.series"
          :show-labels="staffStatusData.labels"
          :colors="['#8FBC8F', '#2F4F4F']"
          donut-size="35%"
        />
      </div>
    </div>
  </div>
</template>

<script>
import Stat from '@/components/Stat.vue'
import Chart from '@/components/Chart.vue'

export default {
  name: 'Dashboard',
  components: {
    Stat,
    Chart
  },
  props: {
    params: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      // Datos para el gráfico de estudiantes (donut)
      studentsData: {
        series: [65, 35],
        labels: ['Activos', 'Inactivos']
      },

      // Datos para pagos de estudiantes
      paymentsData: {
        series: [{
          name: 'Estudiantes',
          data: [85, 70, 55]
        }],
        categories: ['Pagados', 'En proceso', 'En mora']
      },

      // Datos para becas de estudiantes
      scholarshipsData: {
        series: [{
          name: 'Con beca',
          data: [30, 35, 25, 40, 35, 30]
        }, {
          name: 'Sin beca',
          data: [70, 65, 75, 60, 65, 70]
        }]
      },

      // Datos para el nuevo gráfico de promedio de grados
      gradesData: {
        series: [{
          name: 'Promedio',
          data: [95, 82, 88, 92, 96, 85, 89, 93]
        }],
        categories: ['Grado 1', 'Grado 1', 'Grado 1', 'Grado 1', 'Grado 1', 'Grado 1', 'Grado 1', 'Grado 1']
      },

      // Datos para el gráfico de estado del personal (donut)
      staffStatusData: {
        series: [72, 28],
        labels: ['Activo', 'Inactivo']
      }
    }
  }
}
</script>

<style scoped>
/* Hacer que las cards tengan la misma altura */
.charts-row .col-md-4 {
  display: flex;
}

.charts-row .col-md-4 > * {
  flex: 1;
}

.charts-row .col-md-8 {
  display: flex;
}

.charts-row .col-md-8 > * {
  flex: 1;
}
</style>