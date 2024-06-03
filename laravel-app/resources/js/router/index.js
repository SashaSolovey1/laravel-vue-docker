import  { createRouter, createWebHistory} from "vue-router";

import form from "../components/Form.vue"
import comments from "../components/Comments.vue";

const routes = [
    {
        path: "/",
        name: "Home",
        components: {
            default: comments,
            form: form,
        },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
})
export default router
