<template>
  <div class="filters d-flex gap-3 mb-3">
    <input 
      v-model="localFiltros.nombre" 
      @input="emitir" 
      class="form-control" 
      placeholder="Buscar por nombre" 
    />

    <input 
      type="date" 
      v-model="localFiltros.fecha_inicio" 
      @input="emitir" 
      class="form-control" 
    />

    <input 
      type="date" 
      v-model="localFiltros.fecha_fin" 
      @input="emitir" 
      class="form-control" 
    />
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
  filtros: {
    type: Object,
    default: () => ({ nombre: '', fecha_inicio: '', fecha_fin: '' })
  }
});

const emit = defineEmits(['update:filtros']);

const localFiltros = reactive({ ...props.filtros });

const emitir = () => {
  emit('update:filtros', { ...localFiltros });
};
</script>

<style scoped>
.filters {
  flex-wrap: wrap;
}
</style>
