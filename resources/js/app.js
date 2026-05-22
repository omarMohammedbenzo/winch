import './bootstrap';
import { createApp } from 'vue';
import Vue3Toastify from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import App from './components/App.vue';

window.axios.defaults.headers.common['Accept'] = 'application/json';

createApp(App)
    .use(Vue3Toastify, {
        autoClose: 3500,
        position: 'top-center',   // slides down from top-center
        transition: 'slide',
        theme: 'colored',         // colored success/error with a timer bar
        newestOnTop: true,
    })
    .mount('#app');
