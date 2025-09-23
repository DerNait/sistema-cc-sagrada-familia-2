<template>
  <div class="center-container">
    <h3 class="p-3 fw-bold m-0">Pagos</h3>
    <div class="filters-container px-3 py-2 d-flex justify-content-evenly align-items-end gap-2"></div>
	<FileUpload :tiposPago="tipos_pago" ></FileUpload>
  </div>
</template>

<script setup>
import FileUpload from '../components/FileUpload.vue';

const props = defineProps(['tipos_pago'])

</script>