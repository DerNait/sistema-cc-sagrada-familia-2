import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

import './bootstrap';
import { createApp } from 'vue';
import HelloWorld from './components/HelloWorld.vue';

const app = createApp({});
app.component('hello-world', HelloWorld);
app.component('home',home);// El primero nombre donde lo llamaran, el celeste como tienen que llamar al archivo.vue
app.component('students',students);
app.component('profesores',profesores);
app.component('courses',courses);
app.component('payment',payment);
app.component('inventory',inventory);
app.mount('#app');