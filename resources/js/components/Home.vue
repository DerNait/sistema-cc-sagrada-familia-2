<template>
  <div class="home-container d-flex flex-column justify-content-center align-items-center min-vh-100 px-3">
    <div class="text-center mb-5">
      <h1 class="fw-bold">
        ¡Bienvenido, <span class="text-success">{{ usuarioNombre }}</span>!
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
          :key="modulo.id"
          :title="capitalizeFirstLetter(modulo.modulo)"
          :ruta="generarRuta(modulo.modulo)"
          :colorClass="getColorClass(filaIndex * 3 + index)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import CardModulo from '@/components/CardModulo.vue'

const props = defineProps({
  modulos: {
    type: Array,
    required: true
  },
  usuario: {
    type: Object,
    required: true
  }
})

const modulos = computed(() => props.modulos ?? [])
const usuarioNombre = props.usuario?.nombre ?? 'Usuario'

// Agrupar en filas de 3
const chunkArray = (arr, size) => {
  const chunked = []
  for (let i = 0; i < arr.length; i += size) {
    chunked.push(arr.slice(i, i + size))
  }
  return chunked
}

const chunkedModulos = computed(() => chunkArray(modulos.value, 3))

const getColorClass = (index) => {
  const classes = ['custom-green-0', 'custom-green-1', 'custom-green-2', 'custom-green-3']
  return classes[index % classes.length]
}

// Función para generar una ruta sencilla a partir del nombre del módulo
const generarRuta = (moduloNombre) => {
  const rutas = {
    'empleados': '/catalogos/empleados',
    'usuarios': '/catalogos/usuarios',
    'roles': '/catalogos/roles',
    'estudiantes': '/catalogos/estudiantes',
    'productos': '/productos',
    'cursos': '/cursos',
    'actividades': '/catalogos/actividades',
    'home': '/',
    'notas': '/notas',
    'dashboard': '/dashboard'
  }

  return rutas[moduloNombre] ?? '/modulo-no-definido'
}

const capitalizeFirstLetter = (string) => {
  if (!string) return ''
  return string.charAt(0).toUpperCase() + string.slice(1)
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
