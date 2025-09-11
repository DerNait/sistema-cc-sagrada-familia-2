<template>
  <div class="filtro" :class="{ dropup: direction === 'up' }" ref="root">
    <div class="input-group filtro-input">
      <span class="input-group-text little-padding ps-2 bg-white border-0" style="color: #d2d2d2; padding-left: 0.75rem !important;">
        <i class="fa-solid fa-magnifying-glass"></i>
      </span>
      <input
        type="text"
        class="form-control little-padding bg-white border-0 input-no-effects"
        v-model="displayText"
        :placeholder="placeholderText"
        @focus="onFocus"
      />
      <button class="btn btn-outline-filters" type="button" @click="open = !open">
        <i class="fa-solid" :class="caretIcon"></i>
      </button>
    </div>

    <div v-if="open" class="dropdown-menu show filtro-menu">
      <button
        v-for="opt in filtered"
        :key="opt[valueKey]"
        class="dropdown-item"
        :class="{ active: modelValue === opt[valueKey] }"
        @click="select(opt[valueKey])"
      >
        <slot name="option" :option="opt">
          {{ opt[labelKey] }}
        </slot>
      </button>

      <div v-if="!filtered.length" class="dropdown-item disabled text-muted">Sin resultados</div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  modelValue: [String, Number, Array, Object, null],
  options: { type: Array, default: () => [] },
  valueKey: { type: String, default: 'id' },
  labelKey: { type: String, default: 'nombre' },
  placeholder: { type: String, default: 'Selecciona…' },
  // NUEVO: dirección del dropdown
  direction: {
    type: String,
    default: 'down',
    validator: v => ['up', 'down'].includes(v)
  }
});

const emit = defineEmits(['update:modelValue']);

const q = ref('');
const open = ref(false);

const filtered = computed(() => {
  const term = q.value.trim().toLowerCase();
  if (!term) return props.options;
  return props.options.filter(o => String(o[props.labelKey] ?? '').toLowerCase().includes(term));
});

// opción actualmente seleccionada
const selectedOption = computed(() =>
  props.options.find(o => o[props.valueKey] === props.modelValue)
);

// label de la opción seleccionada (o cadena vacía)
const selectedLabel = computed(() =>
  selectedOption.value ? String(selectedOption.value[props.labelKey] ?? '') : ''
);

const placeholderText = computed(() =>
  (!q.value && !selectedLabel.value) ? props.placeholder : ''
);

const displayText = computed({
  get() {
    return q.value !== '' ? q.value : selectedLabel.value;
  },
  set(v) {
    q.value = v ?? '';
  }
});

// Ícono según dirección y estado
const caretIcon = computed(() => {
  if (props.direction === 'up') {
    return open.value ? 'fa-chevron-down' : 'fa-chevron-up';
  }
  return open.value ? 'fa-chevron-up' : 'fa-chevron-down';
});

function select(val) {
  emit('update:modelValue', val);
  open.value = false;
}

const root = ref(null);

function onFocus() {
  open.value = true;
}

function handleDocClick(e) {
  if (!root.value) return
  if (!root.value.contains(e.target)) open.value = false
}

onMounted(() => document.addEventListener('click', handleDocClick))
onBeforeUnmount(() => document.removeEventListener('click', handleDocClick))
</script>

<style scoped>
/* CONTENEDOR */
.filtro { 
  position: relative; 
  min-width: 280px;
}

.little-padding {
  padding: 0.375rem 0.1875rem !important;
}

/* INPUT WRAPPER */
.filtro-input { 
  border-radius: 999px; 
  overflow: hidden;
  border: 1px solid #B5B5B5;
}

.input-no-effects:focus {
  box-shadow: none !important;
}

/* Placeholder */
.form-control::placeholder {
  color: gray;
  font-style: italic;
  opacity: 1;
}

/* Botón */
.btn-outline-filters {
  border-left: 1px solid #B5B5B5;
  color: #B5B5B5;
  background-color: white;
}
.btn-outline-filters:hover { background-color: #ebebeb; }
.btn-outline-filters:active { border: 0px solid transparent; }

/* ===== DROPDOWN ===== */
.filtro-menu {
  width: 100%;
  max-height: 260px;
  overflow: auto;
  margin-top: .25rem; /* default hacia abajo */

  /* navbar-like */
  border: none;
  border-radius: 0.75rem;
  min-width: 180px;
  padding: 0.5rem 0;
  font-size: 0.95rem;
  background-color: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(1rem);
  -webkit-backdrop-filter: blur(1rem);
  box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,.15);

  /* animación de aparición (down) */
  opacity: 0;
  transform: translateY(5px);
  pointer-events: none;
  transition: opacity .2s ease, transform .2s ease;
}

/* Cuando esté “open” */
.filtro .dropdown-menu.show {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

/* Ítems */
.filtro .dropdown-item {
  padding: 0.5rem 1rem;
  font-weight: 500;
}
.filtro .dropdown-item:hover {
  background-color: rgba(0, 43, 85, 0.8);
  color: #fff !important;
}
.filtro .dropdown-item.active {
  background: #83aa6b;
  color: #fff;
}

/* ===== MODO "UP" =====
   Si el contenedor tiene .dropup, coloca el menú arriba.
   Bootstrap ya hace .dropup .dropdown-menu { bottom:100%; top:auto; }
   Ajustamos márgenes/animación para que se vea natural. */
.filtro.dropup .filtro-menu {
  top: auto;        /* clave */
  bottom: 100%;
  margin-top: 0;
  margin-bottom: .25rem;
  /* animación inversa: que aparezca "desde arriba" */
  transform: translateY(-5px);
}
.filtro.dropup .dropdown-menu.show {
  transform: translateY(0);
}
</style>
