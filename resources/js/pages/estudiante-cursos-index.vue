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
      <div v-if="can_create">
        <a 
          href="/admin/cursos?create=1"
          class="card-crear-curso position-relative overflow-hidden h-100 shadow rounded-4 text-decoration-none d-flex flex-column align-items-center justify-content-center"
        >
          <div class="icon-wrapper d-flex align-items-center justify-content-center">
            <i class="fas fa-plus"></i>
          </div>
          <p class="mt-2 mb-0 text-dark">Agregar nuevo curso</p>
        </a>
      </div>
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
  can_create: Boolean,
})

const busqueda = ref('')

const cursosFiltrados = computed(() =>
  props.cursos.filter((curso) =>
    curso.nombre.toLowerCase().includes(busqueda.value.toLowerCase())
  )
)
</script>


<style scoped>
.card-crear-curso {
  min-height: 250px;
  background: rgba(255, 255, 255, 0.5);
  transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
  border: none;
}

.icon-wrapper {
  width: 80px;
  height: 80px;
  font-size: 2.5rem;
  border-radius: 50%;
  border: 2px dashed #6d8f59;
  color: #6d8f59;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.card-crear-curso:hover {
  transform: scale(1.05);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
  background-color: #acff7c2a;
}

.card-crear-curso:hover .icon-wrapper {
  background-color: #6d8f59;
  color: white;
}
</style>
