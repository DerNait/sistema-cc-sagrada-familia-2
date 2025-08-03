<script setup>
import Card from './Card.vue'

const props = defineProps({
  cursos: {
    type: Array,
    default: () => []
  }
})

const verDetalle = (cursoId) => {
  window.location.href = `/cursos/${cursoId}`;
}
</script>

<template>
  <div class="container py-4">
    <h2 class="mb-4">Mis Cursos</h2>

    <div class="row g-3">
      <div class="col-12 col-md-6 col-lg-4" v-for="curso in cursos" :key="curso.id">
        <Card :curso="curso" @click="verDetalle(curso.id)" />
      </div>
    </div>
  </div>
</template>
