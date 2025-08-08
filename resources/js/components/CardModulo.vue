<template>
  <a :href="ruta" class="text-decoration-none d-block h-100">
    <div
      :class="`text-center ${colorClass} shadow d-flex align-items-center justify-content-center card-box w-100 h-100`"
    >
      <div class="d-flex flex-column">
        <i v-if="icon" :class="icon" class="mb-2 icon-size text-white"></i>
        <p class="fw-light fs-1 m-0 text-white">{{ title }}</p>
      </div>
    </div>
  </a>
</template>

<script setup>
defineProps({
  title: String,
  ruta: String,
  icon: String,
  colorClass: { type: String, default: 'custom-green-1' }
})
</script>

<style scoped>
.card-box {
  aspect-ratio: 1 / 1;          /* cuadrado automático */
  max-height: 300px;            /* opcional: tamaño mínimo */
  border-radius: 20px;
  transition: all 0.3s ease;
}

.icon-size {
  font-size: 3rem;
}
</style>
