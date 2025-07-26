<template>
  <div class="p-4">
    <h2 class="text-2xl font-bold mb-4">Curso: {{ curso.nombre }}</h2>

    <div class="bg-white rounded-xl shadow-md p-4">
      <h3 class="text-lg font-semibold mb-2">Actividades</h3>
      
      <table class="min-w-full text-left">
        <thead>
          <tr>
            <th class="py-2 px-4">Actividad</th>
            <th class="py-2 px-4">Nota</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="actividad in actividades" :key="actividad.id">
            <td class="py-2 px-4">{{ actividad.nombre }}</td>
            <td class="py-2 px-4">{{ actividad.nota ?? 'Sin nota' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <button @click="volver" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
      Volver a Cursos
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  curso: Object,
  actividades: Array
})

const volver = () => {
  window.location.href = '/cursos';
}
</script>
