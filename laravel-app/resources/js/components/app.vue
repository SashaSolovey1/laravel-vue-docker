<template>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3>Contact Form</h3>
            </div>
            <div class="card-body">
                <div v-if="replyingToComment" class="alert alert-secondary" role="alert">
                    Ответ на комментарий: "{{ replyingToComment.text.substring(0, 20) }}..."
                </div>
                <form @submit.prevent="submitForm">
                    <!-- Other form fields -->
                    <div class="mb-3">
                        <label for="username" class="form-label">UserName</label>
                        <input type="text" v-model="comment.username" id="username" class="form-control" placeholder="Enter your username" required />
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" v-model="comment.email" id="email" class="form-control" placeholder="Enter your email" required />
                    </div>
                    <div class="mb-3">
                        <label for="homepage" class="form-label">Home page</label>
                        <input type="url" v-model="comment.homepage" id="homepage" class="form-control" placeholder="Enter your homepage (optional)" />
                    </div>
                    <div class="mb-3">
                        <label for="text" class="form-label">Text</label>
                        <textarea v-model="comment.text" id="text" class="form-control" rows="5" placeholder="Enter your message" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">File (Max 10MB)</label>
                        <input type="file" ref="file" id="file" class="form-control" />
                    </div>

                    <!-- CAPTCHA -->
                    <div class="mb-3">
                        <label for="captcha" class="form-label">Captcha</label>
                        <img :src="captchaImage" alt="CAPTCHA Image" class="mb-2" @click="loadCaptcha" style="cursor: pointer;" />
                        <input type="text" v-model="captcha" id="captcha" class="form-control" placeholder="Enter CAPTCHA" required />
                        <input type="hidden" v-model="captchaKey" id="captchaKey" name="key" />
                    </div>

                    <!-- Error message display -->
                    <div v-if="errorMessage" class="alert alert-danger">
                        {{ errorMessage }}
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <div id="app">
        <Comments @setParentId="setParentId" />
    </div>
</template>

<script>
import axios from 'axios';
import Comments from '../components/Comments.vue';

export default {
    name: 'App',
    components: {
        Comments,
    },
    data() {
        return {
            comment: {
                username: '',
                email: '',
                homepage: '',
                text: '',
                parent_id: null
            },
            captcha: '',
            captchaKey: '',
            captchaImage: '',
            errorMessage: '',
            replyingToComment: null,
        }
    },
    computed: {
        isNewComment() {
            return !this.$route.path.includes('edit');
        }
    },
    async created() {
        await this.loadCaptcha();
        if (!this.isNewComment) {
            const response = await axios.get(`/api/comments/${this.$route.params.id}`);
            this.comment = response.data;
        }
    },
    methods: {
        async loadCaptcha() {
            try {
                const response = await axios.get('/captcha/api/math');
                this.captchaImage = response.data.img;
                this.captchaKey = response.data.key; // Assuming the response includes the key
            } catch (error) {
                console.error('Error loading CAPTCHA:', error);
            }
        },
        async submitForm() {
            try {
                const formData = new FormData();
                formData.append('username', this.comment.username);
                formData.append('email', this.comment.email);
                formData.append('homepage', this.comment.homepage);
                formData.append('text', this.comment.text);
                formData.append('parent_id', this.comment.parent_id);
                formData.append('captcha', this.captcha);
                formData.append('key', this.captchaKey); // Pass the CAPTCHA key
                if (this.$refs.file.files[0]) {
                    formData.append('file', this.$refs.file.files[0]);
                }

                console.log(formData);

                const response = await axios.post('/api/comments/validate', formData);
                this.$router.push('/');
            } catch (error) {
                if (error.response && error.response.data.errors) {
                    this.errorMessage = Object.values(error.response.data.errors).flat().join('\n');
                } else {
                    console.error(error);
                }
            }
        },
        async setParentId(parentId) {
            this.comment.parent_id = parentId;

            try {
                const response = await axios.get(`/api/comments/${parentId}`);
                this.replyingToComment = response.data;
            } catch (error) {
                console.error('Error fetching parent comment:', error);
            }

            window.scrollTo(0, 0);
        }
    }
};
</script>

<style>
#app {
    font-family: Arial, Helvetica, sans-serif;
    margin: 20px;
    padding: 20px;
}
</style>
