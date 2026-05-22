import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';

window.axios.defaults.headers.common['Accept'] = 'application/json';

createApp(App).mount('#app');
