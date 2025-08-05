<template>
  <div class="home-container d-flex flex-column justify-content-center align-items-center min-vh-100 px-3">
    <div class="text-center mb-5">
      <h1 class="fw-bold">
        ¡Bienvenido, <span class="text-success">Nombre</span>!
      </h1>
      <h2 class="text-muted">Selecciona una opción para comenzar.</h2>
    </div>

    <div class="container">
      <div
        v-for="(fila, filaIndex) in chunkedModulos"
        :key="filaIndex"
        class="row g-1 mx-auto mb-2"
        style="max-width: 660px; row-gap: 16px;"
      >
        <CardModulo
          v-for="(modulo, index) in fila"
          :key="modulo.title"
          :title="modulo.title"
          :colorClass="getColorClass(filaIndex * 3 + index)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import CardModulo from '@/components/CardModulo.vue'

const modulos = [
  { title: 'Dashboard' },
  { title: 'Catálogos' },
  { title: 'Cursos' },
  { title: 'Secciones' }
]

// Agrupar en filas de 3
const chunkArray = (arr, size) => {
  const chunked = []
  for (let i = 0; i < arr.length; i += size) {
    chunked.push(arr.slice(i, i + size))
  }
  return chunked
}

const chunkedModulos = computed(() => chunkArray(modulos, 3))

const getColorClass = (index) => {
  return `custom-green-${index}`
}
</script>

<style scoped>
:deep(.custom-green-0) {
  background-color: #83AA6B;
  color: white !important;
}
:deep(.custom-green-1) {
  background-color: #739A67;
  color: white !important;
}
:deep(.custom-green-2) {
  background-color: #638A63;
  color: white !important;
}
:deep(.custom-green-3) {
  background-color: #42695B;
  color: white !important;
}

:deep(.custom-green-0:hover),
:deep(.custom-green-1:hover),
:deep(.custom-green-2:hover),
:deep(.custom-green-3:hover) {
  filter: brightness(1.08);
  cursor: pointer;
  transform: translateY(-2px);
  transition: all 0.3s ease;
}

.card-column {
  padding-left: 4px;
  padding-right: 4px;
}
</style>
