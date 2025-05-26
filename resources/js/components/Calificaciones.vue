<template>
  <div class="container-fluid min-vh-100 bg-light py-5">
    <div class="bg-white p-4 rounded shadow">
      <h2 class="mb-4 text-center text-success">Gestión de Calificaciones</h2>

      <!-- Filtros -->
      <div class="row mb-4 align-items-center">
        <div class="col-md-2">
          <label class="form-label">Grado</label>
          <input v-model="grado" class="form-control" placeholder="Ej. 8" />
        </div>
        <div class="col-md-2">
          <label class="form-label">Sección</label>
          <input v-model="seccion" class="form-control" placeholder="Ej. A" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Curso</label>
          <input v-model="curso" class="form-control" placeholder="Ej. Matemáticas" />
        </div>
        <div class="col-md-4 text-end mt-3 mt-md-0">
          <button class="btn btn-success me-2" @click="guardarNotas">
             Guardar
          </button>
          <button class="btn btn-outline-success" @click="generarActa">
             Generar Acta
          </button>
        </div>
      </div>

      <!-- Tabla de calificaciones -->
      <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
          <thead class="table-success">
            <tr>
              <th>#</th>
              <th>Nombre estudiante</th>
              <th>Parcial 1</th>
              <th>Proyecto</th>
              <th>Final</th>
              <th>Promedio</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(estudiante, index) in estudiantes" :key="index">
              <td>{{ index + 1 }}</td>
              <td>{{ estudiante.nombre }}</td>
              <td><input type="number" v-model.number="estudiante.parcial" class="form-control text-center" /></td>
              <td><input type="number" v-model.number="estudiante.proyecto" class="form-control text-center" /></td>
              <td><input type="number" v-model.number="estudiante.final" class="form-control text-center" /></td>
              <td>{{ calcularPromedio(estudiante) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      grado: "8",
      seccion: "A",
      curso: "Matemáticas",
      estudiantes: [
        { nombre: "Emilio Lopez", parcial: 85, proyecto: 92, final: 78 },
        { nombre: "Isaac Ortiz", parcial: 60, proyecto: 55, final: 70 },
      ],
    };
  },
  methods: {
    calcularPromedio(est) {
      const promedio = (est.parcial + est.proyecto + est.final) / 3;
      return promedio.toFixed(1);
    },
    guardarNotas() {
      alert("Notas guardadas correctamente ✅");
    },
    generarActa() {
      alert("Acta generada con éxito 📄");
    },
  },
};
</script>

<style scoped>
.table td input {
  max-width: 80px;
}
</style>
