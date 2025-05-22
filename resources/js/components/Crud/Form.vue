<template>
  <h4>{{ title }}</h4>
  <form method="POST" :action="action">
    <input 
        type="hidden" 
        name="_token" 
        :value="csrf"
    />

    <input 
        v-if="item" 
        type="hidden" 
        name="_method" 
        value="PUT"
    />

    <div v-for="c in Object.values(columns)" :key="c.field" class="mb-3">
        <div v-if="c.editable">
            <label class="form-label">
                {{ c.label }}
            </label>

            <select
              v-if="c.type === 'relation' && c.options"
              class="form-select"
              :name="c.field"
              v-model="form[c.field]"
            >
              <option value="">-- Selecciona --</option>
              <option
                v-for="(label, val) in c.options"
                :key="val"
                :value="val"
              >
                {{ label }}
              </option>
            </select>

            <input 
                v-else-if="c.type === 'numeric'"
                type="number"
                step="any"
                class="form-control"
                :name="c.field"
                v-model.number="form[c.field]"
            />
            <input 
                v-else 
                :type="inputType(c.type)" 
                class="form-control" 
                :name="c.field" 
                v-model="form[c.field]"
            />
        </div>
    </div>

    <button class="btn btn-success">Guardar</button>

    <a :href="previous" class="btn btn-secondary">Cancelar</a>

  </form>
</template>

<script setup>
import { reactive, computed } from 'vue';

const props = defineProps(['item','columns','action']);

const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const form = reactive({});

Object.values(props.columns).forEach(c => {
  form[c.field] = getValue(props.item, c.field) || '';
});

function getValue(obj, path) {
  return obj ? path.split('.').reduce((o, p) => o ? o[p] : null, obj) : '';
}

function inputType(t) {
  if (t === 'date') return 'date';
  if (t === 'time') return 'time';
  if (t === 'datetime') return 'datetime-local';
  return 'text';
}

const title = computed(() => {
  const seg = window.location.pathname.split('/')[1] || '';
  const ent = seg.charAt(0).toUpperCase() + seg.slice(1);
  return (props.item ? 'Editar' : 'Nuevo') + ' ' + ent;
});

const previous = document.referrer || '/';
</script>