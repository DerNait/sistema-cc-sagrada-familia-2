<template>
  <div class="d-flex align-items-center justify-content-between w-100">
    <!-- Icono y título -->
    <div class="d-flex flex-column align-items-center">
      <i :class="`fa ${icon} fa-2x mb-2 text-dark`"></i>
      <span class="fw-semibold text-dark">{{ label }}</span>
    </div>

    <!-- Número grande -->
    <h1 class="fw-bold mb-0 text-dark">{{ value }}</h1>
  </div>
</template>

<script>
export default {
  props: {
    label: {
      type: String,
      required: true
    },
    value: {
      type: [String, Number],
      required: true
    },
    icon: {
      type: String,
      required: true
    }
  }
}
</script>
