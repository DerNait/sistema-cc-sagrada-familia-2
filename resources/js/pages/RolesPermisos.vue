<template>
  <div class="crud-container">
    <div class="d-flex justify-content-between p-3">
      <h4 class="fw-semibold">Rol - Permisos</h4>
      <button
        class="btn btn-primary"
      >
        <i class="fas fa-floppy-disk"></i>
        Guardar
      </button>
    </div>
    <div class="w-100 filters-container" style="height: 20px;"></div>
    <div class="row mx-4 my-5">
      <div
        v-for="modulo in modulos"
        :key="modulo.id"
        class="col-12 col-sm-6 col-md-4 col-lg-3 mb-5"
      >
        <ModuleCard 
          :title="modulo.modulo"
          permissions="Yes"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import ModuleCard from '../components/ModuleCard.vue';

const props = defineProps(['modulos']);

</script>