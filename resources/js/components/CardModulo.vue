<template>
  <div class="col-4 d-flex justify-content-center card-column">
    <router-link
      :to="ruta"
      class="text-decoration-none w-100"
    >
      <div
        :class="`text-center ${colorClass} shadow d-flex align-items-center justify-content-center card-box`"
      >
        <p class="fw-semibold fs-5 m-0 text-white">{{ title }}</p>
      </div>
    </router-link>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  ruta: String,
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
