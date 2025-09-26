<template>
  <div class="d-flex flex-column justify-content-center align-items-center flex-grow-1 px-3 mb-5">
    <div class="text-center mb-5">
      <h2 class="fw-normal">
        ¡Bienvenido, <span class="text-primary">{{ usuarioNombre }}</span>!
      </h2>
      <h3 class="fw-light">Selecciona una opción para comenzar.</h3>
    </div>

    <div class="container">
      <div class="row">
        <div
          v-for="(modulo, index) in modulos"
          :key="modulo.id"
          class="col-12 col-sm-6 col-md-4 mb-4"
        >
          <CardModulo
            :title="formatTitle(modulo.modulo)"
            :ruta="modulo.route_url"
            :icon="modulo.icon"
            :colorClass="getColorClass(index)"
          />
        </div>
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

const getColorClass = (index) => {
  const classes = ['custom-green-0', 'custom-green-1', 'custom-green-2', 'custom-green-3']
  return classes[index % classes.length]
}

function formatTitle(text) {
  const parts = text.split('.')
  return parts
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' - ')
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
</style>
