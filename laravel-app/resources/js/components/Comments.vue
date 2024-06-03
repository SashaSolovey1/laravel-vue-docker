<template>
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
                    <p>{{ comment.text }}</p>
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
                    </div>
                    <span class="repliesSpan" v-if="comment.replies.length > 0">Replies</span>
                    <div v-if="comment.replies.length > 0" class="ml-4 replies">
                        <div v-for="reply in comment.replies" :key="reply.id" class="mb-2 replyBlock">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4>{{ reply.username }}</h4>
                                <small class="text-muted">{{ new Date(reply.created_at).toLocaleString() }}</small>
                            </div>
                            <p>{{ reply.text }}</p>
                            <div class="d-flex align-items-center">
                                <button @click="increaseRating(reply)" class="btn btn-sm btn-outline-success">
                                    <i class="fa fa-thumbs-up"></i>
                                </button>
                                <span class="mx-2">{{ reply.rating }}</span>
                                <button @click="decreaseRating(reply)" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-thumbs-down"></i>
                                </button>

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
</template>

<script>
export default {
    data() {
        return {
            comments: [],
            sortByField: 'created_at',
            sortDirection: 'desc',
            pagination: {}
        };
    },
    created() {
        this.loadComments();
    },
    methods: {
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
        sortBy(field) {
            if (field === this.sortByField) {
                this.sortDirection = this.sortDirection === 'desc' ? 'asc' : 'desc';
            } else {
                this.sortByField = field;
                this.sortDirection = 'desc';
            }
            this.loadComments();
        },
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
        async increaseRating(comment) {
            try {
                const response = await fetch(`/api/comments/${comment.id}/increase-rating`, {
                    method: 'PUT',
                });

                if (!response.ok) {
                    throw new Error('Failed to increase rating');
                }

                await this.loadComments();
            } catch (error) {
                console.error('Error increasing rating:', error);
            }
        },
        async decreaseRating(comment) {
            try {
                const response = await fetch(`/api/comments/${comment.id}/decrease-rating`, {
                    method: 'PUT',
                });

                if (!response.ok) {
                    throw new Error('Failed to decrease rating');
                }

                await this.loadComments();
            } catch (error) {
                console.error('Error decreasing rating:', error);
            }
        },
        replyToComment(parentId) {
            this.$emit('setParentId', parentId);
        }
    },
};
</script>
