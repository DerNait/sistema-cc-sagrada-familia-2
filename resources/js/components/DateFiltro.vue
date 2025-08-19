<template>
  <div class="filtro">
    <div class="input-group filtro-input">
      <!-- Icono -->
      <span
        class="input-group-text little-padding ps-2 bg-white border-0"
        style="color: #d2d2d2; padding-left: 0.75rem !important;"
      >
        <i class="fa-solid fa-calendar-day"></i>
      </span>

      <!-- Input de fecha (nativo) -->
      <input
        type="date"
        class="form-control little-padding bg-white border-0 input-no-effects"
        v-model="internal"
        :placeholder="placeholder"
        @change="onChange"
      />

      <!-- Acciones opcionales -->
      <button
        v-if="showToday"
        class="btn btn-outline-filters"
        type="button"
        @click="setToday"
        title="Hoy"
      >
        <i class="fa-solid fa-bullseye"></i>
      </button>
      <button
        v-if="clearable"
        class="btn btn-outline-filters"
        type="button"
        @click="clear"
        title="Limpiar"
      >
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },      // '' o 'yyyy-mm-dd'
  placeholder: { type: String, default: 'aaaa-mm-dd' },
  clearable: { type: Boolean, default: true },
  showToday: { type: Boolean, default: true },    // botón “Hoy”
});

const emit = defineEmits(['update:modelValue', 'change']);

const internal = ref(props.modelValue || '');

// sincroniza cuando cambie desde el padre
watch(() => props.modelValue, v => {
  if (v !== internal.value) internal.value = v || '';
});

function onChange() {
  emit('update:modelValue', internal.value || '');
  emit('change', internal.value || '');
}

function setToday() {
  const d = new Date();
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  internal.value = `${yyyy}-${mm}-${dd}`;
  onChange();
}

function clear() {
  internal.value = '';
  onChange();
}
</script>

<style scoped>
/* Mismos estilos base que Filtros.vue */
.filtro { 
  position: relative; 
  min-width: 280px;
}

.little-padding {
  padding: 0.375rem 0.1875rem !important;
}

.filtro-input { 
  border-radius: 999px; 
  overflow: hidden;
  border: 1px solid #B5B5B5;
}

.input-no-effects:focus {
  box-shadow: none !important;
}

.form-control::placeholder {
  color: gray;
  font-style: italic;
  opacity: 1;
}

.btn-outline-filters {
  border-left: 1px solid #B5B5B5;
  color: #B5B5B5;
  background-color: white;
}
.btn-outline-filters:hover { background-color: #ebebeb; }
.btn-outline-filters:active { border: 0px solid transparent; }
</style>
