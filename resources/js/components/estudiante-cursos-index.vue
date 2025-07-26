<script setup>
import Card from './Card.vue'

const props = defineProps({
  cursos: Array
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
        <Card @click="verDetalle(curso.id)">
          <h5 class="card-title">{{ curso.nombre }}</h5>
        </Card>
      </div>
    </div>
  </div>
</template>
