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
                <form @submit.prevent="submitForm" enctype="multipart/form-data">
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
                        <textarea v-model="comment.text" id="text" class="form-control" rows="5" ref="text" placeholder="Enter your message" required></textarea>
                        <div class="d-flex align-items-center mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertTag('<i>', '</i>')">[i]</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertTag('<strong>', '</strong>')">[strong]</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertTag('<code>', '</code>')">[code]</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertTag('<a href=&quot;&quot;>', '</a>')">[a]</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">File (Max 10MB)</label>
                        <input name="file" type="file" ref="file" id="file" class="form-control" @change="handleFileChange" />
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

                    <!-- Preview Button -->
                    <button type="button" class="btn btn-secondary me-2" @click="previewMessage" data-bs-toggle="modal" data-bs-target="#previewModal">Preview</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">Message Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-html="previewData.text" class="mb-3"></div>
                    <div v-if="previewData.file">
                        <img v-if="isImage(previewData.file.type)" :src="previewData.file.url" alt="Attached Image" class="img-fluid" />
                        <div v-else>
                            <p>Прикрепленный файл: {{ previewData.file.name }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
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
            previewData: {
                text: '',
                file: null
            }
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
        async insertTag(openTag, closeTag) {
            this.$refs.text.value += openTag + ' ' + closeTag;
        },
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
        },
        handleFileChange() {
            const file = this.$refs.file.files[0];
            if (file) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            const MAX_WIDTH = 320;
                            const MAX_HEIGHT = 240;
                            let width = img.width;
                            let height = img.height;

                            if (width > height) {
                                if (width > MAX_WIDTH) {
                                    height *= MAX_WIDTH / width;
                                    width = MAX_WIDTH;
                                }
                            } else {
                                if (height > MAX_HEIGHT) {
                                    width *= MAX_HEIGHT / height;
                                    height = MAX_HEIGHT;
                                }
                            }

                            const canvas = document.createElement('canvas');
                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, width, height);
                            this.previewData.file = {
                                url: canvas.toDataURL(file.type),
                                name: file.name,
                                type: file.type
                            };
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    this.previewData.file = {
                        name: file.name,
                        type: file.type
                    };
                }
            } else {
                this.previewData.file = null;
            }
        },
        previewMessage() {
            this.previewData.text = this.comment.text;

            const file = this.$refs.file.files[0];
            if (file) {
                this.handleFileChange();
            } else {
                this.previewData.file = null;
            }
        },
        isImage(fileType) {
            return fileType.startsWith('image/');
        }
    }
};
</script>




