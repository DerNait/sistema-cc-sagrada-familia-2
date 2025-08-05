<template>
  <div class="col-4 d-flex justify-content-center card-column">
    <div
      :class="`text-center ${colorClass} shadow d-flex align-items-center justify-content-center card-box`"
    >
      <p class="fw-semibold fs-5 m-0">{{ title }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  colorClass: {
    type: String,
    default: 'custom-green-1'
  }
})
</script>

<style scoped>
.card-box {
  width: 200px;
  height: 200px;
  border-radius: 20px;
  transition: all 0.3s ease;
}
</style>
