<template>
  <div class="dashboard-container p-4">
    <!-- Métricas superiores - alineadas a la derecha -->
    <div class="d-flex justify-content-end gap-5 mb-4" style="margin-top: 2rem; margin-right: 4rem;">
      <Stat
        label="Usuarios"
        :value="124"
        icon="fa-users"
      />

      <Stat
        label="Estudiantes"
        :value="100"
        icon="fa-graduation-cap"
      />

      <Stat
        label="Empleados"
        :value="24"
        icon="fa-briefcase"
      />
    </div>

    <!-- Aquí irían los gráficos después -->
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
  }
}
</script>