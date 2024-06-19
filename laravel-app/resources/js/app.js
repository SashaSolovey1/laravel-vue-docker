import 'bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';

// Import components
import App from './components/app.vue';
import Comments from './components/Comments.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: Comments },
    ]
});

const app = createApp(App);
app.use(router);
app.mount('#app');

