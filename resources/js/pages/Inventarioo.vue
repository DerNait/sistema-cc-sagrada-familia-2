<template>
  <div class="container-fluid p-4">
    <!-- Contenedor principal con el mismo estilo que pagos -->
    <div class="card border-0 shadow-sm bg-light">
      <div class="card-body p-5">
        <!-- Título -->
        <div class="row mb-5">
          <div class="col-12 text-center">
            <h1 class="fw-bold text-dark mb-2" style="font-size: 2.5rem;">
              <i class="fas fa-boxes me-3 text-primary"></i>
              Registro de Movimientos de Inventario
            </h1>
            <p class="text-muted fs-5">Gestiona las entradas y salidas de productos del inventario</p>
          </div>
        </div>

        <!-- Animación de éxito -->
        <div v-if="showSuccessAnimation" 
             class="success-overlay position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
             style="z-index: 9999; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(5px);">
          <div class="success-modal text-center text-white">
            <div class="success-icon-container mb-4">
              <div class="success-circle">
                <i class="fas fa-check success-check"></i>
              </div>
            </div>
            <h2 class="fw-bold mb-3">¡Movimiento Registrado!</h2>
            <p class="fs-5 mb-2">El movimiento se ha guardado correctamente</p>
            <div class="success-details mt-4 p-3 rounded" style="background: rgba(255, 255, 255, 0.1);">
              <div class="row text-start">
                <div class="col-6">
                  <strong>Producto:</strong><br>
                  <span class="text-light">{{ getProductName(lastMovement.producto_id) }}</span>
                </div>
                <div class="col-6">
                  <strong>Tipo:</strong><br>
                  <span class="text-light">{{ lastMovement.tipo === 'entrada' ? '⬇️ Entrada' : '⬆️ Salida' }}</span>
                </div>
              </div>
              <div class="row mt-2 text-start">
                <div class="col-6">
                  <strong>Cantidad:</strong><br>
                  <span class="text-light">{{ lastMovement.cantidad }} unidades</span>
                </div>
                <div class="col-6">
                  <strong>Stock actual:</strong><br>
                  <span class="text-light">{{ selectedProductStock }} unidades</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Mensajes de éxito o error -->
        <div v-if="message" 
             class="row mb-4">
          <div class="col-12">
            <div :class="messageType === 'success' ? 'alert-success' : 'alert-danger'"
                 class="alert d-flex align-items-center shadow-sm border-0"
                 style="border-radius: 15px;">
              <div class="me-3">
                <i :class="messageType === 'success' ? 'fas fa-check-circle fs-3' : 'fas fa-exclamation-triangle fs-3'"></i>
              </div>
              <div>
                <span class="fs-5">{{ message }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Formulario en tarjeta elegante -->
        <div class="row justify-content-center">
          <div class="col-lg-10 col-xl-8">
            <div class="card border-0 shadow-lg" style="border-radius: 20px; background: rgba(255, 255, 255, 0.9);">
              <div class="card-body p-5">
                <form @submit.prevent="submitForm">
                  <div class="row g-4">
                    <!-- Selector de Producto -->
                    <div class="col-md-6">
                      <label for="producto" class="form-label fw-bold fs-5 text-dark mb-3">
                        <i class="fas fa-box text-primary me-2"></i>Producto *
                      </label>
                      <select
                        id="producto"
                        v-model="form.producto_id"
                        @change="getStock"
                        class="form-select form-select-lg shadow-sm"
                        style="border-radius: 15px; border: 2px solid #e9ecef; padding: 1rem 1.5rem;"
                        required
                      >
                        <option value="">Seleccionar producto...</option>
                        <option 
                          v-for="producto in productos" 
                          :key="producto.id" 
                          :value="producto.id"
                        >
                          {{ producto.nombre }}
                        </option>
                      </select>
                    </div>

                    <!-- Selector de Tipo -->
                    <div class="col-md-6">
                      <label for="tipo" class="form-label fw-bold fs-5 text-dark mb-3">
                        <i class="fas fa-exchange-alt text-warning me-2"></i>Tipo de Movimiento *
                      </label>
                      <select
                        id="tipo"
                        v-model="form.tipo"
                        class="form-select form-select-lg shadow-sm"
                        style="border-radius: 15px; border: 2px solid #e9ecef; padding: 1rem 1.5rem;"
                        required
                      >
                        <option value="">Seleccionar tipo...</option>
                        <option value="entrada">
                          Entrada (Agregar stock)
                        </option>
                        <option value="salida">
                          Salida (Reducir stock)
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="row g-4 mt-2">
                    <!-- Campo Cantidad -->
                    <div class="col-md-6">
                      <label for="cantidad" class="form-label fw-bold fs-5 text-dark mb-3">
                        <i class="fas fa-calculator text-success me-2"></i>Cantidad *
                      </label>
                      <input
                        id="cantidad"
                        type="number"
                        v-model.number="form.cantidad"
                        @input="validateCantidad"
                        @focus="cantidadTouched = true"
                        @blur="validateCantidad"
                        min="1"
                        step="1"
                        class="form-control form-control-lg shadow-sm"
                        :class="{ 'is-invalid': cantidadError && cantidadTouched, 'border-danger': cantidadError && cantidadTouched }"
                        style="border-radius: 15px; border: 2px solid #e9ecef; padding: 1rem 1.5rem;"
                        placeholder="Ingrese la cantidad"
                        required
                      >
                      <div v-if="cantidadError && cantidadTouched" class="invalid-feedback fs-6 mt-2">
                        <i class="fas fa-exclamation-circle me-1"></i>{{ cantidadError }}
                      </div>
                    </div>

                    <!-- Campo Descripción -->
                    <div class="col-md-6">
                      <label for="descripcion" class="form-label fw-bold fs-5 text-dark mb-3">
                        <i class="fas fa-comment text-info me-2"></i>Descripción *
                      </label>
                      <textarea
                        id="descripcion"
                        v-model="form.descripcion"
                        rows="3"
                        class="form-control form-control-lg shadow-sm"
                        style="border-radius: 15px; border: 2px solid #e9ecef; padding: 1rem 1.5rem; resize: none;"
                        placeholder="Describe el motivo del movimiento..."
                        required
                      ></textarea>
                    </div>
                  </div>

                  <!-- Botones -->
                  <div class="row mt-5">
                    <div class="col-12">
                      <div class="d-flex gap-4 justify-content-center">
                        <button
                          type="submit"
                          :disabled="loading || !isFormValid"
                          class="btn btn-lg px-5 py-3 fw-bold shadow-lg"
                          :class="loading || !isFormValid ? 'btn-secondary' : 'btn-primary'"
                          style="border-radius: 25px; font-size: 1.1rem; min-width: 200px; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border: none;"
                        >
                          <i v-if="loading" class="fas fa-spinner fa-spin me-2"></i>
                          <i v-else class="fas fa-save me-2"></i>
                          {{ loading ? 'Procesando...' : 'Registrar Movimiento' }}
                        </button>
                        
                        <button
                          type="button"
                          @click="resetForm"
                          class="btn btn-outline-secondary btn-lg px-5 py-3 fw-bold shadow-lg"
                          style="border-radius: 25px; font-size: 1.1rem; min-width: 200px; border: 2px solid #6c757d;"
                        >
                          <i class="fas fa-broom me-2"></i>
                          Limpiar Formulario
                        </button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Inventario",
  props: {
    productos: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      form: {
        producto_id: '',
        tipo: '',
        cantidad: '',
        descripcion: ''
      },
      selectedProductStock: null,
      message: '',
      messageType: '',
      loading: false,
      cantidadError: '',
      cantidadTouched: false
    };
  },
  computed: {
    isFormValid() {
      return this.form.producto_id && 
             this.form.tipo && 
             this.form.cantidad > 0 && 
             this.form.descripcion.trim() &&
             !this.cantidadError;
    }
  },
  methods: {
    // Obtener CSRF token
    getCsrfToken() {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      if (!token) {
        console.error('CSRF token no encontrado')
      }
      return token || ''
    },

    validateCantidad() {
      // Solo validar si el campo ha sido tocado por el usuario
      if (!this.cantidadTouched) {
        return;
      }

      this.cantidadError = '';
      
      if (this.form.cantidad <= 0) {
        this.cantidadError = 'La cantidad debe ser mayor que cero';
        return;
      }
      
      if (!Number.isInteger(this.form.cantidad)) {
        this.cantidadError = 'La cantidad debe ser un número entero';
        return;
      }

      // Validar stock para salidas
      if (this.form.tipo === 'salida' && this.selectedProductStock !== null) {
        if (this.form.cantidad > this.selectedProductStock) {
          this.cantidadError = `No hay suficiente stock. Disponible: ${this.selectedProductStock}`;
        }
      }
    },

    async getStock() {
      if (!this.form.producto_id) {
        this.selectedProductStock = null;
        return;
      }

      try {
        const response = await fetch(`/inventario/stock/${this.form.producto_id}`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.getCsrfToken()
          }
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        this.selectedProductStock = data.stock;
        this.validateCantidad(); // Revalidar cantidad cuando cambie el stock
      } catch (error) {
        console.error('Error al obtener stock:', error);
        this.selectedProductStock = null;
      }
    },

    // Método para obtener el nombre del producto
    getProductName(productoId) {
      const producto = this.productos.find(p => p.id == productoId);
      return producto ? producto.nombre : 'Producto desconocido';
    },

    async submitForm() {
      if (!this.isFormValid) return;

      this.loading = true;
      this.message = '';

      try {
        const formData = new FormData();
        formData.append('producto_id', this.form.producto_id);
        formData.append('tipo', this.form.tipo);
        formData.append('cantidad', this.form.cantidad);
        formData.append('descripcion', this.form.descripcion);

        const response = await fetch('/inventario', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.getCsrfToken()
          },
          body: formData
        });

        if (!response.ok) {
          const errorText = await response.text();
          console.error('Error response:', errorText);
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
          this.message = data.message;
          this.messageType = 'success';
          
          // Actualizar stock
          await this.getStock();
          
          // Limpiar formulario
          this.resetForm();
          
        } else {
          this.message = data.message;
          this.messageType = 'error';
        }
      } catch (error) {
        this.message = 'Error de conexión. Intente nuevamente.';
        this.messageType = 'error';
        console.error('Error:', error);
      }

      this.loading = false;
      
      // Limpiar mensaje después de 5 segundos
      setTimeout(() => {
        this.message = '';
      }, 5000);
    },

    resetForm() {
      this.form = {
        producto_id: '',
        tipo: '',
        cantidad: '',
        descripcion: ''
      };
      this.selectedProductStock = null;
      this.cantidadError = '';
      
      this.cantidadTouched = false;
    }
  },

  watch: {
    'form.tipo'() {
      this.validateCantidad();
    }
  }
};
</script>

<style scoped>

.card {
  border-radius: 12px;
  background-color: rgba(248, 249, 250, 0.4) !important;
  backdrop-filter: blur(8px); /* efecto frosted glass */
}

.form-select:focus,
.form-control:focus {
  border-color: #007bff !important;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15) !important;
  outline: none;
}

.form-select,
.form-control {
  transition: all 0.3s ease;
}

.form-select:hover,
.form-control:hover {
  border-color: #007bff;
  transform: translateY(-1px);
}

.btn {
  transition: all 0.3s ease;
}

.btn:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2) !important;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
  border: none !important;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #0056b3 0%, #004085 100%) !important;
}

.form-label {
  margin-bottom: 0.75rem;
  color: #2c3e50;
}

.alert {
  border: none !important;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.invalid-feedback {
  display: block;
  font-weight: 600;
}


.card-body {
  animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}


.form-control:focus,
.form-select:focus {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(13, 110, 253, 0.15) !important;
}


.form-control:disabled,
.form-select:disabled {
  background-color: rgba(233, 236, 239, 0.5);
  opacity: 0.8;
}
</style>