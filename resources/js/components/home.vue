<template>
    <div class="alert alert-success mt-4 mx-auto w-75 text-center shadow-lg rounded">
      ✅ <strong>Home</strong> Si ves este mensaje, ¡el proyecto está corriendo perfectamente con Laravel + Vue + Vite! 🚀
  
      <div class="mt-4">
        <button class="btn btn-primary" @click="mostrarAlerta">Botón de prueba</button>
      </div>
    </div>
  </template>
  
  <script setup>
  const mostrarAlerta = () => {
    alert('🎉 ¡Este es home!');
  }
  </script>  