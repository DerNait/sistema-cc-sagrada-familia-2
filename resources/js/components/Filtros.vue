<template>
  <div class="filtro" ref="root">
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
        <i class="fa-solid" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
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

function select(val) {
  emit('update:modelValue', val);
  open.value = false;
}

const root = ref(null);

function onFocus() {
  open.value = true;
  //q.value = '';
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

/* Placeholder: ojo, va sobre el input, no sobre .filtro-input */
.form-control::placeholder {
  color: gray;
  font-style: italic;
  opacity: 1;
}

/* Botón (igual que ya tenías) */
.btn-outline-filters {
  border-left: 1px solid #B5B5B5;
  color: #B5B5B5;
  background-color: white;
}
.btn-outline-filters:hover { background-color: #ebebeb; }
.btn-outline-filters:active { border: 0px solid transparent; }

/* ===== DROPDOWN (estilo “navbar”) ===== */
.filtro-menu {
  width: 100%;
  max-height: 260px;
  overflow: auto;
  margin-top: .25rem;

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

  /* animación de aparición */
  opacity: 0;
  transform: translateY(5px);
  pointer-events: none;
  transition: opacity .2s ease, transform .2s ease;
}

/* Cuando esté “open”, que se vea y sea clickeable.
   Con v-if el elemento aparece ya con estas reglas aplicadas. */
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

/* Hover (equivalente a rgba(#002b55,0.8) en SCSS) */
.filtro .dropdown-item:hover {
  background-color: rgba(0, 43, 85, 0.8);
  color: #fff !important;
}

/* Activo (por si lo manejas) */
.filtro .dropdown-item.active {
  background: #83aa6b; /* o tu primario de Bootstrap */
  color: #fff;
}

</style>
