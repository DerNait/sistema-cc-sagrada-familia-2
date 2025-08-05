<template>
  <div class="card h-100">
    <div class="card-header">
      <p class="fw-semibold my-2">{{ title }}</p>
    </div>
    <div class="card-body">
      <div
        v-for="perm in permissions"
        :key="perm.id"
        class="form-check"
      >
        <input
          class="form-check-input"
          type="checkbox"
          :id="'perm-' + perm.id"
          :checked="model[perm.id]"
          @change="model[perm.id] = !model[perm.id]"
        />
        <label class="form-check-label" :for="'perm-' + perm.id">
          {{ perm.nombre }}
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps(['title', 'permissions'])
const model = defineModel()
</script>

