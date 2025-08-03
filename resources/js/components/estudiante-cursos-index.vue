<template>
  <div class="container py-4">
    <h2 class="mb-4">Mis Cursos</h2>

    <Filtro>
      <div class="position-relative w-100">
        <input
          v-model="busqueda"
          class="form-control ps-5"
          placeholder="Buscar curso..."
        />
        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
      </div>
    </Filtro>

    <div class="row row-cols-1 row-cols-md-3 g-4">
      <div
        v-for="curso in cursosFiltrados"
        :key="curso.id"
        class="col"
      >
        <Card :curso="curso" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Filtro from '@/components/Filtro.vue'
import Card from '@/components/Card.vue'

const props = defineProps({
  cursos: Array,
})

const busqueda = ref('')

const cursosFiltrados = computed(() =>
  props.cursos.filter((curso) =>
    curso.nombre.toLowerCase().includes(busqueda.value.toLowerCase())
  )
)
</script>
