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
        <div v-if="(!props.readonly && c.editable) || props.readonly">
          <label class="form-label fs-5 fw-semibold">{{ c.label }}</label>
  
          <select 
            v-if="c.type==='relation' && c.options && !c.isMultiRelation"
            v-model="form[c.field]"
            :disabled="props.readonly || !c.editable"
            class="form-select"
          >
            <option value="">-- Selecciona --</option>
            <option v-for="(label,val) in c.options" :key="val" :value="String(val)">
              {{ label }}
            </option>
          </select>
  
          <div v-else-if="c.type==='relation' && c.isMultiRelation">
            <div v-for="(val, idx) in form[c.field]" :key="idx" class="d-flex gap-2 mb-2">
              <select 
                v-model="form[c.field][idx]" 
                class="form-select"
                :disabled="props.readonly || !c.editable"
              >
                <option value="">-- Selecciona --</option>
                <option 
                  v-for="(label,id) in filteredOptions(c.options, idx, form[c.field])" 
                  :key="id" 
                  :value="id"
                >
                  {{ label }}
                </option>
              </select>
              <button v-if="!props.readonly" type="button" class="btn btn-outline-danger" @click="removeRelation(c.field, idx)">
                <i class="fa fa-trash"></i>
              </button>
            </div>
            <button v-if="!props.readonly" type="button" class="btn btn-sm btn-primary" @click="addRelation(c.field)">
              <i class="fa fa-plus"></i> Agregar {{ c.label }}
            </button>
          </div>
  
          <template v-else-if="c.type === 'password'">
            <!-- Contraseña -->
            <div class="input-group mb-2">
              <input
                :type="showPass ? 'text' : 'password'"
                v-model="form[c.field]"
                class="form-control"
                :readonly="props.readonly || !c.editable"
              />
              <button
                type="button"
                class="btn btn-outline-secondary"
                @click="showPass = !showPass"
              >
                <i :class="showPass ? 'fa fa-eye-slash' : 'fa fa-eye'" />
              </button>
            </div>
  
            <!-- Confirmar contraseña (solo en modo edición / creación) -->
            <div v-if="!props.readonly" class="input-group">
              <input
                :type="showPass ? 'text' : 'password'"
                v-model="form.password_confirmation"
                class="form-control"
                placeholder="Confirmar contraseña"
              />
              <button
                type="button"
                class="btn btn-outline-secondary"
                @click="showPass = !showPass"
              >
                <i :class="showPass ? 'fa fa-eye-slash' : 'fa fa-eye'" />
              </button>
            </div>
          </template>

          <!-- ==== FILE (imagen -> URL) ==== -->
          <div v-else-if="c.type === 'file'">
            <div class="d-flex flex-column gap-2">
              <input
                ref="fileInputs"
                type="file"
                class="form-control d-none"
                :accept="c.options?.accept || 'image/*'"
                :data-field="c.field"
                @change="onFileChange($event, c)"
                :disabled="props.readonly || !c.editable || isUploading[c.field]"
              />
              <div class="input-group">
                <button
                  type="button"
                  class="btn btn-outline-primary"
                  :disabled="props.readonly || !c.editable || isUploading[c.field]"
                  @click="triggerFile(c.field)"
                >
                  <i class="fa fa-upload"></i>
                  {{ isUploading[c.field] ? 'Subiendo...' : (c.options?.buttonText || 'Seleccionar archivo') }}
                </button>

                <input
                  type="text"
                  class="form-control"
                  v-model="form[c.field]"
                  :placeholder="c.options?.placeholder || 'URL del archivo (se llena al subir)'"
                  :readonly="true"
                />
                <button
                  v-if="form[c.field] && !props.readonly"
                  type="button"
                  class="btn btn-outline-danger"
                  @click="clearFile(c.field)"
                >
                  <i class="fa fa-times"></i>
                </button>
              </div>

              <!-- Progreso -->
              <div v-if="isUploading[c.field]" class="progress" style="height: 6px;">
                <div class="progress-bar" role="progressbar" :style="{ width: uploadProgress[c.field] + '%' }"></div>
              </div>

              <!-- Preview si es imagen -->
              <div v-if="preview[c.field]" class="mt-1">
                <img :src="preview[c.field]" alt="preview" class="img-thumbnail" style="max-height: 140px;" />
              </div>
            </div>
          </div>

          <!-- ==== NUEVO: COLOR ==== -->
          <div v-else-if="c.type === 'color'">
            <div class="input-group">
              <span class="input-group-text p-0">
                <input
                  type="color"
                  class="form-control form-control-color border-0"
                  style="width: 2.5rem; height: 100%;"
                  v-model="form[c.field]"
                  :disabled="props.readonly || !c.editable"
                  @input="normalizeHex(c.field)"
                  title="Elegir color"
                />
              </span>
              <input
                type="text"
                class="form-control"
                v-model="form[c.field]"
                :placeholder="c.placeholder || '#RRGGBB'"
                :readonly="props.readonly || !c.editable"
                @blur="normalizeHex(c.field)"
              />
            </div>
          </div>
          
          <input 
            v-else
            :type="inputType(c.type)"
            v-model="form[c.field]"
            class="form-control"
            :readonly="props.readonly || !c.editable" 
          />
        </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
      <button 
        v-if="!props.readonly" 
        class="btn btn-primary flex-grow-1"
        :disabled="busy"
      >
        <div v-if="!busy">
          <i class="fa-solid fa-floppy-disk"></i>
          Guardar
        </div>
        <div v-else>
          <i class="fa-solid fa-spinner fa-spin"></i>
          Guardando...
        </div>
      </button>
      <button 
        type="button" 
        class="btn btn-secondary flex-grow-1" 
        @click="$emit('cancel')"
        :disabled="busy"
      >
        <i class="fa-solid fa-xmark"></i>
        {{ props.readonly ? 'Cerrar' : 'Cancelar' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { reactive, computed, ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  item:     Object,
  columns:  Object,
  action:   String,
  readonly: { type: Boolean, default: false },
  uploadUrl: String,
  defaults: Object, 
});
const emit  = defineEmits(['saved', 'cancel']);

const csrf = document
  .querySelector('meta[name="csrf-token"]')
  .getAttribute('content');

const showPass = ref(false);
const busy = ref(false);

const form = reactive({});

Object.values(props.columns).forEach(c => {
  if (c.isMultiRelation) {
    const val = props.item ? getValue(props.item, c.field)
              : (props.defaults?.[c.field] ?? []);
    form[c.field] = Array.isArray(val) ? [...val] : (val ? [val] : []);
  } else {
    let base = props.item ? getValue(props.item, c.field) ?? ''
              : (props.defaults?.[c.field] ?? '');

    if (c.type === 'relation' && base !== '' && base !== null) {
      base = String(base);
    }

    form[c.field] = base;
  }
});


const hasPassword = computed(() => Object.values(props.columns).some(c => c.type==='password'));
if (hasPassword.value) form.password_confirmation = '';

// Upload states
const uploadProgress = reactive({});
const isUploading = reactive({});
const preview = reactive({});

const fileInputs = ref([]);

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

function addRelation(field) {
  form[field].push('');
}

function removeRelation(field, idx) {
  form[field].splice(idx, 1);
}

function filteredOptions(options, currentIdx, values) {
  const used = values.filter((v, i) => i !== currentIdx && v !== '');
  const filtered = {};
  Object.entries(options).forEach(([id, label]) => {
    if (!used.includes(id)) filtered[id] = label;
  });
  return filtered;
}

onMounted(() => {
  fileInputs.value = Array.from(document.querySelectorAll('input[type="file"][data-field]'));
});

function triggerFile(field) {
  const input = fileInputs.value.find(el => el?.dataset?.field === field);
  input?.click();
}

async function onFileChange(e, c) {
  const file = e.target.files?.[0];
  if (!file) return;

  const field = c.field;
  isUploading[field] = true;
  uploadProgress[field] = 0;

  try {
    const fd = new FormData();
    fd.append('file', file);

    // Enviar path (o mantener compat con 'folder')
    if (c.options?.path)   fd.append('path', c.options.path);
    else if (c.options?.folder) fd.append('path', c.options.folder);

    const targetUrl = c.options?.uploadUrl || props.uploadUrl;

    const { data } = await axios.post(targetUrl, fd, {
      headers: { 'Content-Type': 'multipart/form-data', 'X-CSRF-TOKEN': csrf },
      onUploadProgress: (evt) => {
        if (evt.total) uploadProgress[field] = Math.round((evt.loaded * 100) / evt.total);
      }
    });

    form[field]    = data.url_decorated ?? data.path ?? '';
    preview[field] = data.url ?? ('/storage/' + (data.path ?? ''));

  } catch (err) {
    console.error(err);
    Swal.fire({
      icon: 'error',
      title: 'Error al subir archivo',
      text: 'No se pudo subir el archivo. Por favor intenta nuevamente.',
      confirmButtonText: 'Entendido'
    });
  } finally {
    isUploading[field] = false;
  }
}

function clearFile(field) {
  form[field] = '';
  preview[field] = '';
  uploadProgress[field] = 0;
}

function looksLikeImage(url) {
  return /\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/i.test(url);
}

async function handleSubmit() {
  if (props.readonly) return;  

  busy.value = true;
  const method = props.item ? 'put' : 'post';
  
  try {
    console.log('trying to save in: ' + props.action);
    const { data } = await axios[method](props.action, form);
    busy.value = false;
    emit('saved', data);        // devuelve el registro recién grabado
  } catch (err) {
    console.error(err);
    busy.value = false;
    
    // Mostrar errores de validación con SweetAlert2
    if (err.response && err.response.status === 422) {
      const errors = err.response.data.errors || {};
      const errorMessages = Object.values(errors).flat();
      
      if (errorMessages.length > 0) {
        Swal.fire({
          icon: 'error',
          title: 'Error de validación',
          html: errorMessages.map(msg => `• ${msg}`).join('<br>'),
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#3085d6'
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error de validación',
          text: 'Por favor verifica los datos ingresados.',
          confirmButtonText: 'Entendido'
        });
      }
    } else if (err.response && err.response.data && err.response.data.message) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: err.response.data.message,
        confirmButtonText: 'Entendido'
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error al guardar',
        text: 'Por favor intenta nuevamente.',
        confirmButtonText: 'Entendido'
      });
    }
  }
}

const entity = computed(() => {
  const segments = window.location.pathname.split('/').filter(Boolean);
  const lastSegment = segments[segments.length - 1] || '';
  return lastSegment.charAt(0).toUpperCase() + lastSegment.slice(1);
});

function normalizeHex(field) {
  let v = (form[field] || '').toString().trim();
  if (!v) { form[field] = '#000000'; return; }
  if (!v.startsWith('#')) v = '#' + v;
  if (/^#([0-9a-fA-F]{3})$/.test(v)) {
    v = '#' + v.slice(1).split('').map(x => x + x).join('');
  }
  if (!/^#([0-9a-fA-F]{6})$/.test(v)) {
    // si no es válido, puedes forzar un valor por defecto si quieres
  }
  form[field] = v;
}

</script>

<style scoped>
/* 100 % alto para que flex funcione dentro del drawer */
form { height: 100%; }

/* Afinar la altura del input color dentro del input-group */
.input-group .form-control-color {
  min-width: 2.5rem;
}
</style>
