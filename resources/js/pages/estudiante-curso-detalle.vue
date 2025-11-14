<template>
  <div class="p-4">
    <h2 class="mb-4">Curso: {{ curso.nombre }}</h2>
    <p><strong>Usuario:</strong> {{ modo }}</p>

    <!-- FILTROS -->
    <div class="d-flex gap-3 mb-4 flex-wrap filtro-container">
      <!-- Curso -->
      <div class="filtro-combo flex-fill">
        <label class="form-label fs-5">Curso</label>
        <div class="input-icon-wrapper w-100">
          <i class="fas fa-search icon-left"></i>
          <select class="form-select ps-5" v-model="selectedCurso">
            <option value="">Todos</option>
            <option v-for="curso in cursos" :key="curso.id" :value="curso.nombre">
              {{ curso.nombre }}
            </option>
          </select>
        </div>
      </div>

      <!-- Sección -->
      <div class="filtro-combo flex-fill">
        <label class="form-label fs-5">Sección</label>
        <div class="input-icon-wrapper w-100">
          <i class="fas fa-search icon-left"></i>
          <select class="form-select ps-5" v-model="selectedSeccion">
            <option value="">Todas</option>
            <option v-for="seccion in secciones" :key="seccion.id" :value="seccion.nombre">
              {{ seccion.nombre }}
            </option>
          </select>
        </div>
      </div>

      <!-- Estudiante -->
      <div class="filtro-combo flex-fill">
        <label class="form-label fs-5">Estudiante</label>
        <div class="input-icon-wrapper w-100">
          <i class="fas fa-search icon-left"></i>
          <select class="form-select ps-5" v-model="selectedEstudiante">
            <option value="">Todos</option>
            <option v-for="estudiante in estudiantes" :key="estudiante.id" :value="estudiante.nombre">
              {{ estudiante.nombre }}
            </option>
          </select>
        </div>
      </div>

      <!-- Actividad -->
      <div class="filtro-combo flex-fill">
        <label class="form-label fs-5">Actividad</label>
        <div class="input-icon-wrapper w-100">
          <i class="fas fa-search icon-left"></i>
          <select class="form-select ps-5" v-model="selectedActividad">
            <option value="">Todas</option>
            <option v-for="actividad in actividades" :key="actividad.id" :value="actividad.nombre">
              {{ actividad.nombre }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- TABLA -->
    <div v-if="actividades.length > 0">
      <h3>Actividades</h3>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Fecha inicio</th>
            <th>Fecha fin</th>
            <th>Nota</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="actividad in actividades" :key="actividad.id">
            <td>{{ actividad.nombre }}</td>
            <td>{{ actividad.fecha_inicio ?? 'N/A' }}</td>
            <td>{{ actividad.fecha_fin ?? 'N/A' }}</td>
            <td>{{ actividad.nota ?? 'Sin nota' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else>
      <p>No hay actividades para este curso.</p>
    </div>

    <button @click="volver" class="btn btn-primary mt-3">Volver a cursos</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  curso: Object,
  actividades: Array,
  modo: String,
})

const cursos = [
  { id: 1, nombre: 'Matemáticas' },
  { id: 2, nombre: 'Historia' },
  { id: 3, nombre: 'Ciencias' },
]

const secciones = [
  { id: 1, nombre: 'A' },
  { id: 2, nombre: 'B' },
  { id: 3, nombre: 'C' },
]

const estudiantes = [
  { id: 1, nombre: 'Juan Pérez' },
  { id: 2, nombre: 'María López' },
  { id: 3, nombre: 'Carlos Ruiz' },
]

const selectedCurso = ref('')
const selectedSeccion = ref('')
const selectedEstudiante = ref('')
const selectedActividad = ref('')

const volver = () => {
  window.location.href = '/cursos'
}
</script>

<style scoped>
.filtro-container > .filtro-combo {
  width: 20%;
  min-width: 200px;
}

.input-icon-wrapper {
  position: relative;
}

.icon-left {
  position: absolute;
  top: 50%;
  left: 12px;
  transform: translateY(-50%);
  color: #aaa;
  pointer-events: none;
  z-index: 2;
}

.form-select {
  padding-left: 2.2rem !important;
  padding-right: 1.2rem !important;
  height: 38px;
}
</style>
