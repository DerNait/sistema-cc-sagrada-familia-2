import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

import './bootstrap';
import { createApp } from 'vue';
import HelloWorld from './components/HelloWorld.vue';

const app = createApp({});
app.component('hello-world', HelloWorld);
app.mount('#app');