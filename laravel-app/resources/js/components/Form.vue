<template>

</template>

<script>
import axios from 'axios';


export default {

    data() {
        return {
            comment: {
                username: '',
                email: '',
                home_page: '',
                text: '',
            }
        }
    },
    computed: {
        isNewComment() {
            return !this.$route.path.includes('edit');
        }
    },
    async created() {
        this.loadCaptcha();
        if (!this.isNewComment) {
            const response = await axios.get(`/api/comments/${this.$route.params.id}`);
            this.product = response.data;
        }
    },
    methods: {

        async submitForm() {
            try {
                if (this.isNewComment) {
                    await axios.post('/api/products', this.product);
                } else {
                    await axios.put(`/api/products/${this.$route.params.id}`, this.product);
                }
                this.$router.push('/');
            } catch (error) {
                console.error(error);
            }
        }
    }

};
</script>

<style scoped>
.container {
    max-width: 600px;
    margin: 0 auto;
}

.card {
    border-radius: 0.5rem;
}

.card-header {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
}
</style>
