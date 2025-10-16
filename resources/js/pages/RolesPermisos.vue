<template>
  <div class="crud-container">
    <div class="d-flex justify-content-between p-3">
      <h4 class="fw-semibold">{{ rol.nombre }} – Permisos</h4>
      <button 
        class="btn btn-primary" 
        @click="save"
      >
        <i class="fas fa-floppy-disk"></i> Guardar
      </button>
    </div>
    <div class="filters-container d-flex px-3 py-4 d-flex justify-content-between align-items-center">
      <div></div>
      <div style="max-width: 270px; flex:1">
        <SearchBar v-model="globalSearch" />
      </div>
    </div>
    <div class="row mx-4 my-5">
      <div
        v-for="mod in filteredModulos"
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
import { reactive, computed, ref } from 'vue'
import Swal from 'sweetalert2'
import RoleModuleCard from '../components/RoleModuleCard.vue'
import SearchBar from '../components/SearchBar.vue'

const props = defineProps(['rol', 'modulos'])

const globalSearch = ref('')

const seleccion = reactive(
  props.modulos.reduce((acc, m) => {
    m.permisos.forEach(p => { acc[p.id] = p.checked })
    return acc
  }, {})
)

const filteredModulos = computed(() => {
  if (!globalSearch.value) return props.modulos
  return props.modulos.filter(mod => 
    mod.modulo.toLowerCase().includes(globalSearch.value.toLowerCase())
  )
})

async function save () {
  const elegidos = Object.entries(seleccion)
    .filter(([_, v]) => v)
    .map(([k]) => Number(k))

  try {
    await axios.post(`/admin/roles/${props.rol.id}/permisos`, {
      modulo_permiso_ids: elegidos
    })

    Swal.fire({
      icon: 'success',
      title: 'Permisos actualizados',
      text: 'Los permisos se han guardado correctamente.',
      timer: 2500,
      timerProgressBar: true,
      showConfirmButton: false
    })
  } catch (error) {
    console.error(error)
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudieron actualizar los permisos.'
    })
  }
}
</script>
