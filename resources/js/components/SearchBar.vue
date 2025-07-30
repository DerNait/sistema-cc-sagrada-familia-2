<template>
  <div class="search-wrapper">
    <input
      class="search-input"
      :placeholder="placeholder"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <i class="fa-solid fa-magnifying-glass search-icon"></i>
  </div>
</template>

<script setup>
const props = defineProps({
  /* v-model */
  modelValue: { type: String, default: '' },
  /* por si un día quieres otro texto */
  placeholder: { type: String, default: 'Buscar' }
});
</script>

<style scoped>
.search-wrapper {            /* contenedor */
  position: relative;
  width: 100%;
}
.search-input {              /* input bonito */
  width: 100%;
  padding: .3rem 2.75rem .3rem 1rem;   /* hueco para la lupa */
  border: 1px solid #B5B5B5;
  border-radius: 0.8rem;
  font-size: 1rem;         /* como el mock‑up */
  color: #777;
  outline: none;
}
.search-icon {               /* lupa */
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #B5B5B5;
  font-size: 1rem;
}
</style>
