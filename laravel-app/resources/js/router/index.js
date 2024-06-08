import  { createRouter, createWebHistory} from "vue-router";

import comments from "../components/Comments.vue";

const routes = [
    {
        path: "/",
        name: "Home",
        components: {
            default: comments,
        },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
})
export default router
