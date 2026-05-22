import './bootstrap';
import { createApp } from 'vue';
import ActiveOrders from './components/ActiveOrders.vue';

// Ask the API for JSON; Accept-Language drives the bilingual (ar/en) responses.
window.axios.defaults.headers.common['Accept'] = 'application/json';

createApp(ActiveOrders).mount('#app');
