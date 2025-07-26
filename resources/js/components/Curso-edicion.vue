<template>
  <div class="container mt-4">
    <h2 class="mb-4">Editar Notas del Curso</h2>

    <div v-if="actividades.length === 0">
      <div class="alert alert-info">No hay actividades registradas aún.</div>
    </div>

    <div v-else>
      <div v-for="actividad in actividades" :key="actividad.id" class="mb-5">
        <h5 class="mb-3">{{ actividad.nombre }}</h5>
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th>Estudiante</th>
                <th>Nota</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="estudiante in actividad.estudiantes" :key="estudiante.id">
                <td>{{ estudiante.nombre }}</td>
                <td>
                  <input
                    type="number"
                    class="form-control"
                    v-model="estudiante.nota"
                    min="0"
                    max="100"
                    step="0.1"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <button class="btn btn-primary" @click="guardarCambios">
        Guardar Cambios
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Simulación de datos (esto luego será reemplazado con props o fetch)
const actividades = ref([
  {
    id: 1,
    nombre: 'Actividad 1 - Ensayo',
    estudiantes: [
      { id: 1, nombre: 'Juan Pérez', nota: 85 },
      { id: 2, nombre: 'Ana Gómez', nota: 92 }
    ]
  },
  {
    id: 2,
    nombre: 'Actividad 2 - Exposición',
    estudiantes: [
      { id: 1, nombre: 'Juan Pérez', nota: 75 },
      { id: 2, nombre: 'Ana Gómez', nota: 88 }
    ]
  }
])

function guardarCambios() {
  console.log('Notas actualizadas:', actividades.value)
  alert('Cambios guardados (simulado)')
}
</script>

<style scoped>
h2 {
  font-weight: bold;
}
</style>
