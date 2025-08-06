<template>
  <div class="container py-4">
    <h2 class="mb-4 text-center fw-normal">Selecciona el <span class="fw-semibold text-primary">curso</span> que deseas ver</h2>

    <Filtro>
      <div class="input-group">
        <input 
          type="text" 
          class="form-control" 
          placeholder="Buscar curso..." 
          v-model="busqueda"
        />
        <span class="input-group-text">
          <i class="fas fa-search"></i>
        </span>
      </div>
    </Filtro>

    <div class="row row-cols-1 row-cols-md-4 g-4">
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
