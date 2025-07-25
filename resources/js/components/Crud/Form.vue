<template>
  <form @submit.prevent="handleSubmit" class="h-100 d-flex flex-column">
    <!-- CSRF -->
    <input type="hidden" name="_token" :value="csrf" />
    <input v-if="item" type="hidden" name="_method" value="PUT" />

    <h5 class="mb-4">
      {{ item ? 'Editar' : 'Nuevo' }} {{ entity }}
    </h5>

    <div class="flex-grow-1 overflow-auto pe-1">
      <div v-for="c in Object.values(columns)" :key="c.field" class="mb-3">
        <label class="form-label">{{ c.label }}</label>

        <select 
          v-if="c.type==='relation' && c.options"
          v-model="form[c.field]"
          :disabled="props.readonly || !c.editable"
          class="form-select"
        >
          <option value="">-- Selecciona --</option>
          <option v-for="(label,val) in c.options" :key="val" :value="val">
            {{ label }}
          </option>
        </select>

        <input 
          v-else
          :type="inputType(c.type)"
          v-model="form[c.field]"
          class="form-control"
          :readonly="props.readonly || !c.editable" 
        />
      </div>
    </div>

    <div class="mt-4 d-flex gap-2">
      <button 
        v-if="!props.readonly" 
        class="btn btn-primary flex-grow-1"
      >
        <i class="fa-solid fa-floppy-disk"></i>
        Guardar
      </button>
      <button type="button" class="btn btn-secondary flex-grow-1" @click="$emit('cancel')">
        <i class="fa-solid fa-xmark"></i>
        {{ props.readonly ? 'Cerrar' : 'Cancelar' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { reactive, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  item:     Object,
  columns:  Object,
  action:   String,
  readonly: { type: Boolean, default: false }
});
const emit  = defineEmits(['saved', 'cancel']);

const csrf = document
  .querySelector('meta[name="csrf-token"]')
  .getAttribute('content');

const form = reactive({});
Object.values(props.columns).forEach(c =>
  (form[c.field] = props.item ? getValue(props.item, c.field) ?? '' : '')
);

function inputType(t) {
  if (t === 'date') return 'date';
  if (t === 'time') return 'time';
  if (t === 'datetime') return 'datetime-local';
  if (t === 'number' || t === 'numeric') return 'number';
  return 'text';
}
function getValue(obj, path) {
  return path.split('.').reduce((o, p) => (o ? o[p] : null), obj);
}

async function handleSubmit() {
  if (props.readonly) return;  

  const method = props.item ? 'put' : 'post';
  
  try {
    console.log('trying to save in: ' + props.action);
    const { data } = await axios[method](props.action, form);
    emit('saved', data);        // devuelve el registro recién grabado
  } catch (err) {
    console.error(err);
    alert('Error al guardar');
  }
}

const entity = computed(() => {
  const seg = window.location.pathname.split('/')[1] || '';
  return seg.charAt(0).toUpperCase() + seg.slice(1);
});
</script>

<style scoped>
/* 100 % alto para que flex funcione dentro del drawer */
form { height: 100%; }
</style>
