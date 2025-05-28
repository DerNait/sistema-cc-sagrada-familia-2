<template>
  <div class="container py-5 bg-light min-vh-100">
    <div class="text-center mb-5">
      <img src="/images/logo_.jpg" alt="Logo" class="mb-3" style="width: 120px;" />
      <h1 class="fw-bold">Nuestros Cursos</h1>
      <p class="text-muted fs-5">Explora los cursos disponibles y empieza a aprender hoy.</p>
    </div>

    <div class="row">
      <div
        class="col-md-6 col-lg-4 mb-4"
        v-for="curso in cursos"
        :key="curso.id"
      >
        <div class="card h-100 shadow-sm">
          <img
            :src="curso.imagen"
            class="card-img-top"
            alt="Imagen del curso"
            style="height: 180px; object-fit: cover;"
          />
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ curso.nombre }}</h5>
            <p class="card-text text-muted">{{ curso.descripcion }}</p>
            <div class="mt-auto">
              <button class="btn btn-primary w-100">
                Ver más
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Cursos',
  data() {
    return {
      cursos: [
        {
          id: 1,
          nombre: 'Introducción a la Programación',

          
        },
        {
          id: 2,
          nombre: 'Matematicas',
          descripcion: 'Matematicas moderna',
          
        },
        {
          id: 3,
          nombre: 'Ciencias Naturales',

          
        },
        {
          id: 4,
          nombre: 'Literatura',
 
          
        },
        {
          id: 5,
          nombre: 'Educación Física',

          
        },
        {
          id: 6,
          nombre: 'Robotica',

          
        },
      ],
    };
  },
};
</script>

<style scoped>
h1 {
  font-size: 2.5rem;
}

.card {
  border-radius: 1rem;
}

.btn-primary {
  background-color: #4f46e5;
  border: none;
}

.btn-primary:hover {
  background-color: #4338ca;
}
</style>
