import 'sweetalert2/dist/sweetalert2.min.css'
import 'bootstrap';

import './bootstrap';
import { createApp } from 'vue';
import InicioSesion from './pages/InicioSesion.vue';
import Registro from './pages/Registro.vue';
import Pagos from './pages/Pagos.vue';
import Inventario from './pages/Inventario.vue';
import Empleados from './components/Empleados.vue';
import Planilla from './components/Planilla.vue';
import AgregacionInventario from './pages/AgregacionInventario.vue';
import Calificaciones from './pages/Calificaciones.vue';
import estudianteCursosIndex from './pages/estudiante-cursos-index.vue';
import estudianteCursosDetalle from './pages/estudiante-curso-detalle.vue';
import EstudianteCurso from './pages/EstudianteCurso.vue';
import DocenteCurso from './pages/DocenteCurso.vue';
import Home from './pages/Home.vue';
import Dashboard from './pages/Dashboard.vue';

// CRUD
import Table from './components/Crud/Table.vue';
import Form from './components/Crud/Form.vue';

// Roles
import RolesPermisos from './pages/RolesPermisos.vue';

import EstudiantePago from './pages/EstudiantePago.vue';
import AdminPago from './pages/AdminPago.vue';
const app = createApp({});

// El primero nombre donde lo llamaran, el celeste como tienen que llamar al archivo.vue
app.component('inicio-sesion',InicioSesion);
app.component('registro',Registro);
app.component('agregacion-inventario',AgregacionInventario);
app.component('inventario',Inventario);
app.component('planilla',Planilla);
app.component('empleados',Empleados);
app.component('calificaciones',Calificaciones);
app.component('estudiante-cursos-index', estudianteCursosIndex);
app.component('estudiante-cursos-detalle', estudianteCursosDetalle);
app.component('estudiante-curso', EstudianteCurso);
app.component('docente-curso', DocenteCurso);
app.component('home', Home);
app.component('dashboard', Dashboard);

// CRUD
app.component('crud-table', Table);
app.component('crud-form', Form);

// Roles
app.component('roles-permisos', RolesPermisos);

app.component('estudiante-pago', EstudiantePago);
app.component('admin-pago', AdminPago);

app.mount('#app');