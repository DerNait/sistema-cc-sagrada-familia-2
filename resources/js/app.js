import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

import './bootstrap';
import { createApp } from 'vue';
import HelloWorld from './components/HelloWorld.vue';
import Homepadres from './components/Homepadres.vue';
import Homeprofesores from './components/Homeprofesores.vue';

const app = createApp({});

app.component('hello-world', HelloWorld);
app.component('home-padres',Homepadres);// El primero nombre donde lo llamaran, el celeste como tienen que llamar al archivo.vue
app.component('home-profesores',Homeprofesores);
// app.component('students',Students);
// app.component('profesores',Profesores);
// app.component('courses',Courses);
// app.component('payment',Payment);
// app.component('inventory',Inventory);
app.mount('#app');