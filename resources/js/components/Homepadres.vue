<template>
  <div>
    <!-- Encabezado -->
    <header class="header">
      <h1>Centro cultural Sagrada Familia II</h1>
    </header>

    <!-- Lista de pagos -->
    <main class="main-content">
      <section class="payment-list">
        <div class="payment-item">
          <h2>Marzo 2025</h2>
          <span class="status-paid">Pagado</span>
        </div>
        <div class="payment-item">
          <h2>Abril 2025</h2>
          <span class="status-pending">Pendiente: </span>
          <button class="upload-button">Subir comprobante</button>
        </div>
      </section>
    </main>

    <!-- Menú de navegación -->
    <footer class="footer">
      <nav>
        <ul class="nav-menu">
          <li>🏠 Home</li>
          <li>💰 Pagos</li>
          <li>⚠️ Notificaciones</li>
        </ul>
      </nav>
    </footer>
  </div>
</template>

<script setup>
</script>

<style scoped>
/* Aqui van los styles cuando los use */
</style>
