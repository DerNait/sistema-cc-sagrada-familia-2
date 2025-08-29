<template>
  <div class="dashboard-container p-4">
    
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

    
    <div v-else class="text-center my-5">
      <p class="text-muted">Cargando datos del dashboard...</p>
    </div>

    <!-- Gráficos -->
    <div v-if="params && params.charts" class="row g-4 mt-4 charts-row">
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
    <div v-if="params && params.charts" class="row g-4 mt-1 charts-row">
      <!-- Gráfico de promedio de grados -->
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
  computed: {
    // Datos para el gráfico de estudiantes (donut) - Con beca/Sin beca
    studentsData() {
      if (!this.params?.charts?.scholarship_donut) {
        return { series: [], labels: [] }
      }
      
      const data = this.params.charts.scholarship_donut
      return {
        series: data.map(item => item.value),
        labels: data.map(item => item.label)
      }
    },

    // Datos para pagos de estudiantes (bar) - Pagados/No pagados
    paymentsData() {
      if (!this.params?.charts?.donut_paid_unpaid) {
        return { series: [], categories: [] }
      }
      
      const data = this.params.charts.donut_paid_unpaid
      return {
        series: [{
          name: 'Estudiantes',
          data: data.map(item => item.value)
        }],
        categories: data.map(item => item.label)
      }
    },

    // Datos para becas de estudiantes (bar horizontal) - Con beca/Sin beca
    scholarshipsData() {
      if (!this.params?.charts?.scholarship_donut) {
        return { series: [] }
      }
      
      const data = this.params.charts.scholarship_donut
      const conBeca = data.find(item => item.label === 'Con beca')?.value || 0
      const sinBeca = data.find(item => item.label === 'Sin beca')?.value || 0
      
      return {
        series: [{
          name: 'Con beca',
          data: [conBeca, conBeca, conBeca, conBeca, conBeca, conBeca]
        }, {
          name: 'Sin beca',
          data: [sinBeca, sinBeca, sinBeca, sinBeca, sinBeca, sinBeca]
        }]
      }
    },

    // Datos para promedio de grados (bar)
    gradesData() {
      if (!this.params?.charts?.grades_avg_bar) {
        return { series: [], categories: [] }
      }
      
      const data = this.params.charts.grades_avg_bar
      return {
        series: [{
          name: 'Promedio',
          data: data.map(item => item.value)
        }],
        categories: data.map(item => item.label)
      }
    },

    // Datos para estado del personal (donut) - Pagado/No pagado este mes
    staffStatusData() {
      if (!this.params?.staff_pay_status) {
        return { series: [], labels: [] }
      }
      
      const data = this.params.staff_pay_status
      return {
        series: [data.paid, data.unpaid],
        labels: ['Pagado', 'No pagado']
      }
    }
  }
}
</script>

<style scoped>
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