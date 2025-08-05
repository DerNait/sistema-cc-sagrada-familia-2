<template>
  <div class="card">
    <div class="card-header">
      <p class="fw-semibold my-2">
        {{ title }}
      </p>
    </div>
    <div class="card-body">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" value="" id="checkDefault">
        <label class="form-check-label" for="checkDefault">
          Permisos
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>

const props = defineProps(['title', 'permissions']);

</script>

