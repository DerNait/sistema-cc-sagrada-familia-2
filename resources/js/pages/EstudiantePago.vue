<template>
  <div class="center-container">
    <h3 class="p-3 fw-bold m-0">Pagos</h3>
    <div class="filters-container px-3 py-2 d-flex justify-content-evenly align-items-end gap-2"></div>
    <div class="d-flex justify-content-center text-center">
      <div class="d-flex flex-column">
        <p :class="['fs-5 my-2', bgClass]">
          <strong>ESTADO:</strong> {{ estado.mensaje ?? 'Sin datos' }}
        </p>
        <p 
          v-if="estado.pendiente_extra" 
          class="badge text-bg-warning text-light"
        >
          {{ estado.pendiente_extra_mensaje ?? 'Sin datos' }}
        </p>
      </div>
    </div>
	  <FileUpload 
      :tiposPago="tipos_pago"
      :pendienteExtra="estado.pendiente_extra"
      :precioGrado="precio_grado"
      accept="image/jpeg,image/png,image/jpg"
      @uploaded="onUploaded" 
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import FileUpload from '../components/FileUpload.vue'
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps(['tipos_pago', 'estado_pago', 'precio_grado']);

const estado = ref('');
estado.value = props.estado_pago;

const bgClass = computed(() => {
  switch (estado.value.estado) {
    case 'vigente':   return 'text-primary'
    case 'pendiente': return 'text-warning'
    case 'vencido':   return 'text-danger'
    default:          return 'text-secondary'
  }
})

function onUploaded(data) {
  showSuccess('Éxito', 'El pago ha sido registrado correctamente.', 3000);

  estado.value = data.estado_pago;
}

function showSuccess(title, text, timer = 3000) {
  Swal.fire({
    icon: 'success',
    title: title,
    text: text,
    timer: timer,
    timerProgressBar: true,
    showConfirmButton: timer === false
  });
}
</script>