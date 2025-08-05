<template>
  <div class="crud-container">
    <div class="d-flex justify-content-between p-3">
      <h4 class="fw-semibold">Rol – Permisos</h4>
      <button 
        class="btn btn-primary" 
        @click="save"
      >
        <i class="fas fa-floppy-disk"></i> Guardar
      </button>
    </div>

    <div class="row mx-4 my-5">
      <div
        v-for="mod in props.modulos"
        :key="mod.id"
        class="col-12 col-sm-6 col-md-4 col-lg-3 mb-5"
      >
        <RoleModuleCard
          :title="mod.modulo"
          :permissions="mod.permisos"
          v-model="seleccion"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import RoleModuleCard from '../components/RoleModuleCard.vue'

const props = defineProps(['rol', 'modulos'])

// Estructura reactiva que irá al backend
const seleccion = reactive(
  props.modulos.reduce((acc, m) => {
    m.permisos.forEach(p => { acc[p.id] = p.checked })
    return acc
  }, {})
)

async function save () {
  const elegidos = Object.entries(seleccion)
    .filter(([_, v]) => v)
    .map(([k]) => Number(k))

  await axios.post(`/catalogos/roles/${props.rol.id}/permisos`, {
    modulo_permiso_ids: elegidos
  })

  alert('Permisos actualizados') // placeholder
}
</script>