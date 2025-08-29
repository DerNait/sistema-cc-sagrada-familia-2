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

    <!-- Aquí irán los gráficos después -->
    <div class="row g-4">
      <div class="col-12">
        <p class="text-muted">Aquí irán los gráficos...</p>
      </div>
    </div>
  </div>
</template>

<script>
import Stat from '@/components/Stat.vue'

export default {
  name: 'Dashboard',
  components: {
    Stat
  },
  props: {
    params: {
      type: Object,
      required: true
    }
  }
}
</script>
