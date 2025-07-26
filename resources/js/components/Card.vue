<template>
  <div class="card shadow-sm h-100" style="cursor: pointer;" @click="$emit('click')">
    <img 
      src="https://cdn-icons-png.flaticon.com/512/8832/8832880.png" 
      class="card-img-top" 
      alt="Icono libro abierto"
      style="object-fit: contain; height: 200px; padding: 20px;"
    />
    <div class="card-body d-flex flex-column">
      <slot />
    </div>
  </div>
</template>

<script setup>
</script>
