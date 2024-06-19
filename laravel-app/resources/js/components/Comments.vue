<template>
    <div>
        <!-- Блок новых комментариев -->
        <div class="container mt-5" v-if="newComments.length > 0">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h3>New Comments</h3>
                </div>
                <div class="card-body">
                    <div v-for="comment in newComments" :key="comment.id" class="mb-3">
                        <!-- Проверка и отображение родительского комментария, если это ответ -->
                        <template v-if="comment.parentComment">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4>{{ comment.parentComment.username }}</h4>
                                <small class="text-muted">{{ new Date(comment.parentComment.created_at).toLocaleString() }}</small>
                            </div>
                            <p v-html="comment.parentComment.text"></p>
                        </template>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>{{ comment.username }} <span class="badge badge-success">Новый комментарий</span></h4>
                            <small class="text-muted">{{ new Date(comment.created_at).toLocaleString() }}</small>
                        </div>
                        <p v-html="comment.text"></p>
                        <div class="d-flex align-items-center">
                            <button @click="increaseRating(comment)" class="btn btn-sm btn-outline-success">
                                <i class="fa fa-thumbs-up"></i>
                            </button>
                            <span class="mx-2">{{ comment.rating }}</span>
                            <button @click="decreaseRating(comment)" class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-thumbs-down"></i>
                            </button>
                            <button @click="replyToComment(comment.id)" class="btn btn-sm btn-outline-secondary">
                                Reply
                            </button>
                            <template v-if="comment.file_path && isImage(comment.file_path)">
                                <div class="attachment">
                                    <a class="example-image-link" :href="getFilePath(comment.file_path)" data-lightbox="example-set">
                                        <img class="example-image" :src="getFilePath(comment.file_path)" alt="Comment Image" style="max-width: 200px;">
                                    </a>
                                </div>
                            </template>
                            <template v-else-if="comment.file_path">
                                <div class="attachment">
                                    <a :href="getFilePath(comment.file_path)" download>Download File</a>
                                </div>
                            </template>
                        </div>
                        <hr />
                    </div>
                </div>
            </div>
        </div>

        <!-- Основной блок комментариев -->
        <div class="container mt-5">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h3>Comments</h3>
                    <div>
                        <button @click="sortBy('created_at')" class="btn btn-sm btn-outline-dark">
                            Date <i :class="getSortIcon('created_at')"></i>
                        </button>
                        <button @click="sortBy('username')" class="btn btn-sm btn-outline-dark">
                            Username <i :class="getSortIcon('username')"></i>
                        </button>
                        <button @click="sortBy('email')" class="btn btn-sm btn-outline-dark">
                            Email <i :class="getSortIcon('email')"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div v-for="comment in comments" :key="comment.id" class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>{{ comment.username }}</h4>
                            <small class="text-muted">{{ new Date(comment.created_at).toLocaleString() }}</small>
                        </div>
                        <p v-html="comment.text"></p>
                        <div class="d-flex align-items-center">
                            <button @click="increaseRating(comment)" class="btn btn-sm btn-outline-success">
                                <i class="fa fa-thumbs-up"></i>
                            </button>
                            <span class="mx-2">{{ comment.rating }}</span>
                            <button @click="decreaseRating(comment)" class="btn btn-sm btn-outline-danger">
                                <i class="fa fa-thumbs-down"></i>
                            </button>
                            <button @click="replyToComment(comment.id)" class="btn btn-sm btn-outline-secondary">
                                Reply
                            </button>
                            <template v-if="comment.file_path && isImage(comment.file_path)">
                                <div class="attachment">
                                    <a class="example-image-link" :href="getFilePath(comment.file_path)" data-lightbox="example-set">
                                        <img class="example-image" :src="getFilePath(comment.file_path)" alt="Comment Image" style="max-width: 200px;">
                                    </a>
                                </div>
                            </template>
                            <template v-else-if="comment.file_path">
                                <div class="attachment">
                                    <a :href="getFilePath(comment.file_path)" download>Download File</a>
                                </div>
                            </template>
                        </div>
                        <span class="repliesSpan" v-if="comment.replies.length > 0">Replies</span>
                        <div v-if="comment.replies.length > 0" class="ml-4 replies">
                            <div v-for="reply in comment.replies" :key="reply.id" class="mb-2 replyBlock">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4>{{ reply.username }}</h4>
                                    <small class="text-muted">{{ new Date(reply.created_at).toLocaleString() }}</small>
                                </div>
                                <p v-html="reply.text"></p>
                                <div class="d-flex align-items-center">
                                    <button @click="increaseRating(reply)" class="btn btn-sm btn-outline-success">
                                        <i class="fa fa-thumbs-up"></i>
                                    </button>
                                    <span class="mx-2">{{ reply.rating }}</span>
                                    <button @click="decreaseRating(reply)" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-thumbs-down"></i>
                                    </button>
                                    <template v-if="reply.file_path && isImage(reply.file_path)">
                                        <div class="attachment">
                                            <a class="example-image-link" :href="getFilePath(reply.file_path)" data-lightbox="example-set">
                                                <img class="example-image" :src="getFilePath(reply.file_path)" alt="Comment Image" style="max-width: 200px;">
                                            </a>
                                        </div>
                                    </template>
                                    <template v-else-if="reply.file_path">
                                        <div class="attachment">
                                            <a :href="getFilePath(reply.file_path)" download>Download File</a>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <hr />
                    </div>
                </div>
            </div>
            <nav aria-label="Page navigation example" v-if="pagination.last_page > 1">
                <ul class="pagination">
                    <li v-for="page in pagination.links" :key="page.label" class="page-item" :class="{ active: page.active, disabled: !page.url }">
                        <a class="page-link" @click.prevent="loadComments(page.url)" href="#">
                            <span v-if="page.label === '&laquo; Previous'">
                                <i class="fas fa-arrow-left"></i> Previous
                            </span>
                            <span v-else-if="page.label === 'Next &raquo;'">
                                Next <i class="fas fa-arrow-right"></i>
                            </span>
                            <span v-else>
                                {{ page.label }}
                            </span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
import 'lightbox2/dist/css/lightbox.min.css';
import 'lightbox2/dist/js/lightbox-plus-jquery.js';
import Echo from '../bootstrap';

export default {
    data() {
        return {
            comments: [], // Основные комментарии
            newComments: [], // Новые комментарии
            sortByField: 'created_at', // Поле сортировки
            sortDirection: 'desc', // Направление сортировки
            pagination: {}, // Пагинация
        };
    },
    created() {
        this.loadComments();
    },
    methods: {
        // Проверка, является ли файл изображением
        isImage(file_path) {
            const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp'];
            const ext = file_path.substring(file_path.lastIndexOf('.')).toLowerCase();
            return imageExtensions.includes(ext);
        },
        // Получение полного пути к файлу
        getFilePath(file_path) {
            return `${file_path}`;
        },
        // Загрузка комментариев с сервера
        loadComments(url = `/api/comments?sortBy=${this.sortByField}&sortDirection=${this.sortDirection}`) {
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    this.comments = data.data;
                    this.pagination = data;
                })
                .catch(error => {
                    console.error('Error loading comments:', error);
                });
        },
        // Сортировка комментариев
        sortBy(field) {
            if (field === this.sortByField) {
                this.sortDirection = this.sortDirection === 'desc' ? 'asc' : 'desc';
            } else {
                this.sortByField = field;
                this.sortDirection = 'desc';
            }
            this.loadComments();
        },
        // Получение иконки сортировки
        getSortIcon(field) {
            if (field === this.sortByField) {
                return {
                    'fa': true,
                    'fa-sort-up': this.sortDirection === 'asc',
                    'fa-sort-down': this.sortDirection === 'desc',
                };
            }
            return {
                'fa': true,
                'fa-sort': true,
            };
        },
        // Увеличение рейтинга комментария
        async increaseRating(comment) {
            try {
                const response = await fetch(`/api/comments/${comment.id}/increase-rating`, {
                    method: 'PUT',
                });

                if (!response.ok) {
                    throw new Error('Failed to increase rating');
                }

                const updatedComment = await response.json();
                this.updateCommentRating(updatedComment);
            } catch (error) {
                console.error('Error increasing rating:', error);
            }
        },
        // Уменьшение рейтинга комментария
        async decreaseRating(comment) {
            try {
                const response = await fetch(`/api/comments/${comment.id}/decrease-rating`, {
                    method: 'PUT',
                });

                if (!response.ok) {
                    throw new Error('Failed to decrease rating');
                }

                const updatedComment = await response.json();
                this.updateCommentRating(updatedComment);
            } catch (error) {
                console.error('Error decreasing rating:', error);
            }
        },
        // Обработка новых комментариев
        async handleNewComment(comment) {
            if (comment.parent_id) {
                const parentResponse = await fetch(`/api/comments/${comment.parent_id}`);
                const parentComment = await parentResponse.json();
                comment.parentComment = parentComment;
            }
            this.newComments.unshift(comment);
        },
        // Обновление рейтинга комментария
        updateCommentRating(updatedComment) {
            // Обновление рейтинга в основном блоке комментариев
            const commentIndex = this.comments.findIndex(comment => comment.id === updatedComment.id);
            if (commentIndex !== -1) {
                this.comments[commentIndex].rating = updatedComment.rating;
            }

            // Обновление рейтинга в блоке новых комментариев
            const newCommentIndex = this.newComments.findIndex(comment => comment.id === updatedComment.id);
            if (newCommentIndex !== -1) {
                this.newComments[newCommentIndex].rating = updatedComment.rating;
            }

            // Обновление рейтинга в ответах
            this.comments.forEach(comment => {
                const replyIndex = comment.replies.findIndex(reply => reply.id === updatedComment.id);
                if (replyIndex !== -1) {
                    comment.replies[replyIndex].rating = updatedComment.rating;
                }
            });

            this.newComments.forEach(comment => {
                const replyIndex = comment.replies.findIndex(reply => reply.id === updatedComment.id);
                if (replyIndex !== -1) {
                    comment.replies[replyIndex].rating = updatedComment.rating;
                }
            });
        },
        // Ответ на комментарий
        replyToComment(parentId) {
            this.$emit('setParentId', parentId);
        }
    },
    mounted() {
        Echo.channel('comments')
            .listen('CommentCreated', (e) => {
                console.log('New comment created:', e.comment);
                this.handleNewComment(e.comment);
            })
            .listen('CommentRatingChanged', (e) => {
                console.log('Comment rating changed:', e.comment);
                this.updateCommentRating(e.comment);
            });
    }
};
</script>
