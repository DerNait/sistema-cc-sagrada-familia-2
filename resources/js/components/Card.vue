<template>
  <a 
    :href="`/cursos/${curso.id}`"
    class="card text-white position-relative overflow-hidden h-100 shadow rounded-4 text-decoration-none"
    style="cursor: pointer; border: none; min-height: 250px;"
  >
    <div 
      class="position-absolute top-0 start-0 w-100 h-100 bg-cover bg-center"
      v-if="curso.imagen"
      :style="{ backgroundImage: `url(/storage/${curso.imagen})` }"
    ></div>

    <div 
      class="position-absolute top-0 start-0 w-100 h-100"
      :style="{ backgroundColor: overlayColor }"
    ></div>

    <div class="position-relative d-flex flex-column justify-content-center align-items-center h-100 px-3 text-center">
      <i 
        v-if="curso.icono"
        :class="['fa', curso.icono]" 
        class="mb-2"
        style="font-size: 6rem;"
      ></i>

      <div class="position-absolute bottom-0 start-0 p-3 fs-4 fw-semibold text-white">
        {{ curso.nombre }}
      </div>
    </div>
  </a>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  curso: {
    type: Object,
    required: true
  }
})

console.log('Datos recibidos en Card.vue:', props.curso)

function hexToRgba(hex, alpha) {
  if (!hex || !/^#([0-9A-F]{6})$/i.test(hex)) return `rgba(0, 0, 0, ${alpha})`
  const r = parseInt(hex.slice(1, 3), 16)
  const g = parseInt(hex.slice(3, 5), 16)
  const b = parseInt(hex.slice(5, 7), 16)
  return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

const overlayColor = computed(() => hexToRgba(props.curso.color, 0.8))
</script>

<style scoped>
.bg-cover {
  background-size: cover;
}
.bg-center {
  background-position: center;
}
.text-shadow {
  text-shadow: 1px 1px 3px rgba(0,0,0,0.7);
}
.card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
  transform: scale(1.05);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
}
</style>
