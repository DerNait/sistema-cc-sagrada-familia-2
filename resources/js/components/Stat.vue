<template>
  <div class="d-flex align-items-center justify-content-between">
    <!-- Icono y título -->
    <div class="d-flex flex-column align-items-center">
      <i :class="`fa ${icon} fa-2x mb-2 text-dark`"></i>
      <span class="fw-semibold text-dark">{{ label }}</span>
    </div>

    <!-- Número grande -->
    <h1 class="mb-0 text-dark ms-2">{{ formattedValue }}</h1>
  </div>
</template>

<script>
export default {
  name: 'Stat',
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
    },
    formatNumber: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    formattedValue() {
      if (!this.formatNumber) return this.value;
      
      const num = Number(this.value);
      if (isNaN(num)) return this.value;
      
      // Formatear números grandes
      if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
      } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
      }
      
      return num.toLocaleString();
    }
  }
}
</script>

<style scoped>
/* Estilos para las métricas del dashboard */
.d-flex {
  min-width: 120px;
}

h1 {
  font-size: 4rem;
  line-height: 1;
}

span {
  font-size: 1.5rem;
  line-height: 1.2;
}

.fa-2x {
  font-size: 2em;
}
</style>