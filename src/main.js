import './assets/css/main.css'

import { createApp } from 'vue'
import router from './router'
import App from './App.vue'
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';
import axios from 'axios'

axios.defaults.baseURL = 'http://localhost:8000/api';

const app = createApp(App);
app.config.globalProperties.$axios = axios;

app.use(router);
app.mount("#app");
