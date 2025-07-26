import 'sweetalert2/dist/sweetalert2.min.css'
import 'bootstrap';

import './bootstrap';
import { createApp } from 'vue';
import HelloWorld from './components/HelloWorld.vue';
import Homepadres from './components/Homepadres.vue';
import Homeprofesores from './components/Homeprofesores.vue';
import InicioSesion from './components/InicioSesion.vue';
import Registro from './components/Registro.vue';
import Estudiantes from './components/Estudiantes.vue';
import Profesores from './components/Profesores.vue';
import Cursos from './components/Cursos.vue';
import Pagos from './components/Pagos.vue';
import Inventario from './components/Inventario.vue';
import Empleados from './components/Empleados.vue';
import Planilla from './components/Planilla.vue';
import EdicionUsuarios from './components/EdicionUsuarios.vue';
import AgregacionInventario from './components/AgregacionInventario.vue';
import Calificaciones from './components/Calificaciones.vue';
import estudianteCursosIndex from './components/estudiante-cursos-index.vue';
import estudianteCursosDetalle from './components/estudiante-curso-detalle.vue';

// CRUD
import Table from './components/Crud/Table.vue';
import Form from './components/Crud/Form.vue';

const app = createApp({});

app.component('hello-world', HelloWorld);
app.component('home-padres', Homepadres);// El primero nombre donde lo llamaran, el celeste como tienen que llamar al archivo.vue
app.component('home-profesores', Homeprofesores);
app.component('inicio-sesion',InicioSesion);
app.component('registro',Registro);
app.component('estudiantes',Estudiantes);
app.component('profesores',Profesores);
app.component('cursos',Cursos);
app.component('pagos',Pagos);
app.component('agregacion-inventario',AgregacionInventario);
app.component('inventario',Inventario);
app.component('planilla',Planilla);
app.component('empleados',Empleados);
app.component('edicion-usuarios',EdicionUsuarios);
app.component('calificaciones',Calificaciones);
app.component('estudiante-cursos-index', estudianteCursosIndex);
app.component('estudiante-cursos-detalle', estudianteCursosDetalle);

// CRUD
app.component('crud-table', Table);
app.component('crud-form', Form);

// app.component('students',Students);
// app.component('profesores',Profesores);
// app.component('courses',Courses);
app.mount('#app');