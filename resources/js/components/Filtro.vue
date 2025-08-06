<template>
  <div class="filtro-container">
    <div class="filtro-inner">
      <slot />
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.filtro-container {
  margin-bottom: 1rem;
  display: flex;
  justify-content: center;
}

.filtro-inner {
  max-width: 300px;
  width: 100%;
}
</style>
