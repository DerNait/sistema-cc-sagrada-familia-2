import 'sweetalert2/dist/sweetalert2.min.css'
import 'bootstrap';

import './bootstrap';
import { createApp } from 'vue';
import HelloWorld from './components/HelloWorld.vue';
import Homepadres from './components/Homepadres.vue';
import Homeprofesores from './components/Homeprofesores.vue';
import InicioSesion from './pages/InicioSesion.vue';
import Registro from './pages/Registro.vue';
import Estudiantes from './components/Estudiantes.vue';
import Profesores from './components/Profesores.vue';
import Cursos from './components/Cursos.vue';
import Pagos from './pages/Pagos.vue';
import Inventario from './pages/Inventario.vue';
import Empleados from './components/Empleados.vue';
import Planilla from './components/Planilla.vue';
import EdicionUsuarios from './components/EdicionUsuarios.vue';
import AgregacionInventario from './components/AgregacionInventario.vue';
import Calificaciones from './components/Calificaciones.vue';
import estudianteCursosIndex from './components/estudiante-cursos-index.vue';
import estudianteCursosDetalle from './components/estudiante-curso-detalle.vue';
import EstudianteCurso from './pages/EstudianteCurso.vue';
import DocenteCurso from './pages/DocenteCurso.vue';
import CursoEdicion from './components/Curso-edicion.vue';
import Home from './components/Home.vue';
import Dashboard from './components/Dashboard.vue';

// CRUD
import Table from './components/Crud/Table.vue';
import Form from './components/Crud/Form.vue';

// Roles
import RolesPermisos from './pages/RolesPermisos.vue';

import CargarSeccion from './Pages/CargarSeccion.vue';

import EstudiantePago from './pages/EstudiantePago.vue';
import AdminPago from './pages/AdminPago.vue';
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
app.component('curso-edicion', CursoEdicion);
app.component('estudiante-curso', EstudianteCurso);
app.component('docente-curso', DocenteCurso);
app.component('cargar-seccion', CargarSeccion);
app.component('home', Home);
app.component('dashboard', Dashboard);

// CRUD
app.component('crud-table', Table);
app.component('crud-form', Form);

// Roles
app.component('roles-permisos', RolesPermisos);

app.component('estudiante-pago', EstudiantePago);
app.component('admin-pago', AdminPago);

// app.component('students',Students);
// app.component('profesores',Profesores);
// app.component('courses',Courses);
app.mount('#app');