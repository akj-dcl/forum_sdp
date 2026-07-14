<script setup lang="ts">
import { Head, Link, usePage, useForm, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    posts: any[];
    channels: any[];
    directoryUsers: any[];
    activeChannel: any | null;
    highlights: any[];
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);
const userRoles = computed(() => page.props.auth?.user?.role || []);

const hasRole = (role: string) => {
    if (Array.isArray(userRoles.value)) {
        return userRoles.value.includes(role);
    }
    return userRoles.value === role;
};

// Breadcrumbs definition
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.activeChannel ? `Channel: ${props.activeChannel.name}` : 'Home Feed',
        href: props.activeChannel ? `/dashboard?channel=${props.activeChannel.slug}` : '/dashboard',
    },
];

// Search Query for colleagues
const searchColleagueQuery = ref('');

// Filter colleagues based on search query
const filteredColleagues = computed(() => {
    return props.directoryUsers.filter(c => 
        c.name.toLowerCase().includes(searchColleagueQuery.value.toLowerCase()) ||
        (c.role && c.role.toLowerCase().includes(searchColleagueQuery.value.toLowerCase()))
    );
});

import EmojiStickerPicker from '@/components/EmojiStickerPicker.vue';

// Form state using Inertia useForm
const form = useForm({
    content: '',
    channel_id: props.activeChannel?.id || '',
    attachment: null as File | null,
    link_url: '',
    link_title: '',
    sticker_url: ''
});

const fileInput = ref<HTMLInputElement | null>(null);
const fileAccept = ref('*/*');
const attachmentPreviewUrl = ref<string | null>(null);
const attachmentName = ref<string>('');

const showAttachmentDropdown = ref(false);
const showLinkInput = ref(false);
const showPostEmojiPicker = ref(false);

const triggerFileInput = () => {
    fileInput.value?.click();
};

const selectAttachmentType = (type: 'image' | 'video' | 'audio' | 'file' | 'link') => {
    showAttachmentDropdown.value = false;
    if (type === 'link') {
        showLinkInput.value = true;
        removeAttachment();
    } else {
        showLinkInput.value = false;
        removeLink();
        if (type === 'image') fileAccept.value = 'image/*';
        else if (type === 'video') fileAccept.value = 'video/*';
        else if (type === 'audio') fileAccept.value = 'audio/*';
        else fileAccept.value = '*/*';
        
        setTimeout(() => {
            triggerFileInput();
        }, 100);
    }
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        form.attachment = file;
        attachmentName.value = file.name;
        removeLink();
        removeSticker();
        
        if (file.type.startsWith('image/')) {
            attachmentPreviewUrl.value = URL.createObjectURL(file);
        } else {
            attachmentPreviewUrl.value = null;
        }
    }
};

const removeAttachment = () => {
    form.attachment = null;
    attachmentName.value = '';
    attachmentPreviewUrl.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

const cancelLinkInput = () => {
    showLinkInput.value = false;
    removeLink();
};

const removeLink = () => {
    form.link_url = '';
    form.link_title = '';
};

const removeSticker = () => {
    form.sticker_url = '';
};

// Post Emojis / Sticker functions
const handleSelectPostEmoji = (emoji: string) => {
    form.content += emoji;
};

const handleSelectPostGif = (gif: { url: string; name: string }) => {
    removeAttachment();
    removeLink();
    form.sticker_url = gif.url;
    showPostEmojiPicker.value = false;
};

const handleSelectPostSticker = (sticker: { url: string; name: string }) => {
    removeAttachment();
    removeLink();
    form.sticker_url = sticker.url;
    showPostEmojiPicker.value = false;
};

const postTextarea = ref<HTMLTextAreaElement | null>(null);

const focusPostTextarea = () => {
    form.content = '';
    postTextarea.value?.focus();
};

// Handle window custom events from AppLayout global sidebar
const handleFocusTextarea = () => {
    focusPostTextarea();
};

const attachmentContainer = ref<HTMLElement | null>(null);
const emojiPickerContainer = ref<HTMLElement | null>(null);

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    
    // Check post creator attachment dropdown click outside
    if (attachmentContainer.value && !attachmentContainer.value.contains(target)) {
        showAttachmentDropdown.value = false;
    }
    
    // Check post creator emoji picker click outside
    if (emojiPickerContainer.value && !emojiPickerContainer.value.contains(target)) {
        showPostEmojiPicker.value = false;
    }
    
    // Check edit post attachment click outside
    const isInsideEditAttachment = target.closest('.edit-attachment-container');
    if (!isInsideEditAttachment) {
        editForm.value.showAttachmentDropdown = false;
    }
    
    // Check edit post emoji click outside
    const isInsideEditEmoji = target.closest('.edit-emoji-picker-container');
    if (!isInsideEditEmoji) {
        editForm.value.showPostEmojiPicker = false;
    }
    
    // Check comment emoji pickers click outside
    const isInsideCommentPicker = target.closest('.comment-emoji-picker-container');
    if (!isInsideCommentPicker) {
        activeCommentEmojiPickerPostId.value = null;
    }
};

onMounted(() => {
    window.addEventListener('focus-post-textarea', handleFocusTextarea);
    document.addEventListener('click', handleClickOutside);
    
    // Check if query param redirect exists from other pages
    const params = new URLSearchParams(window.location.search);
    if (params.get('create') === 'true') {
        focusPostTextarea();
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('focus-post-textarea', handleFocusTextarea);
    document.removeEventListener('click', handleClickOutside);
});

// Helper to get initials for fallback avatars
const getInitials = (name: string) => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
};

// Engagement handlers via Inertia request
const toggleReaction = (postId: number, type: 'like' | 'bulb') => {
    router.post(`/posts/${postId}/react`, { type }, {
        preserveScroll: true
    });
};

const handleCreatePost = () => {
    if (!form.content.trim() && !form.attachment && !form.link_url && !form.sticker_url) return;

    form.post('/posts', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('content', 'attachment', 'link_url', 'link_title', 'sticker_url');
            attachmentName.value = '';
            attachmentPreviewUrl.value = null;
            showLinkInput.value = false;
            if (fileInput.value) fileInput.value.value = '';
        }
    });
};

// Commenting state & handlers
const commentInputs = ref<Record<number, string>>({});
const activeCommentFormPostId = ref<Record<number, boolean>>({});

const toggleCommentForm = (postId: number) => {
    activeCommentFormPostId.value[postId] = !activeCommentFormPostId.value[postId];
};

const handleAddComment = (postId: number) => {
    const text = commentInputs.value[postId];
    if (!text || !text.trim()) return;

    router.post(`/posts/${postId}/comments`, { content: text }, {
        preserveScroll: true,
        onSuccess: () => {
            commentInputs.value[postId] = '';
        }
    });
};

const activeCommentEmojiPickerPostId = ref<number | null>(null);

const toggleCommentEmojiPicker = (postId: number) => {
    if (activeCommentEmojiPickerPostId.value === postId) {
        activeCommentEmojiPickerPostId.value = null;
    } else {
        activeCommentEmojiPickerPostId.value = postId;
    }
};

const handleSelectCommentEmoji = (postId: number, emoji: string) => {
    const currentText = commentInputs.value[postId] || '';
    commentInputs.value[postId] = currentText + emoji;
};

const handleSelectCommentGif = (postId: number, gif: { url: string; name: string }) => {
    const currentText = commentInputs.value[postId] || '';
    commentInputs.value[postId] = (currentText + ` [GIF: ${gif.name}](${gif.url})`).trim();
    activeCommentEmojiPickerPostId.value = null;
};

const handleSelectCommentSticker = (postId: number, sticker: { url: string; name: string }) => {
    const currentText = commentInputs.value[postId] || '';
    commentInputs.value[postId] = (currentText + ` [Sticker: ${sticker.name}](${sticker.url})`).trim();
    activeCommentEmojiPickerPostId.value = null;
};

const parseComment = (content: string) => {
    if (!content) return { text: '', mediaUrl: null, mediaType: null, firstUrl: null };
    
    const mediaRegex = /\[(Sticker|GIF):\s*([^\]]+)\]\((https?:\/\/[^\)]+)\)/i;
    const mediaMatch = content.match(mediaRegex);
    
    let text = content;
    let mediaUrl = null;
    let mediaType = null;
    
    if (mediaMatch) {
        text = content.replace(mediaRegex, '').trim();
        mediaType = mediaMatch[1].toLowerCase();
        mediaUrl = mediaMatch[3];
    }

    const urlRegex = /(https?:\/\/[^\s]+)/;
    const urlMatch = text.match(urlRegex);
    const firstUrl = urlMatch ? urlMatch[0] : null;

    return {
        text,
        mediaUrl,
        mediaType,
        firstUrl
    };
};

const linkify = (text: string) => {
    if (!text) return '';
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    return text.replace(urlRegex, (url) => {
        return `<a href="${url}" target="_blank" class="text-primary hover:underline font-semibold break-all">${url}</a>`;
    });
};

// Edit/Delete post state
const editingPostId = ref<number | null>(null);
const editForm = ref({
    content: '',
    attachment: null as File | null,
    attachment_name: '',
    link_url: '',
    link_title: '',
    sticker_url: '',
    delete_attachment: false,
    showLinkInput: false,
    showAttachmentDropdown: false,
    showPostEmojiPicker: false,
    attachmentPreviewUrl: null as string | null,
    fileAccept: '*/*',
    hasOriginalAttachment: false,
    originalAttachmentName: '',
    originalAttachmentType: '',
    originalAttachmentPath: ''
});

const startEditPost = (post: any) => {
    editingPostId.value = post.id;
    editForm.value = {
        content: post.content,
        attachment: null,
        attachment_name: '',
        link_url: '',
        link_title: '',
        sticker_url: '',
        delete_attachment: false,
        showLinkInput: false,
        showAttachmentDropdown: false,
        showPostEmojiPicker: false,
        attachmentPreviewUrl: null,
        fileAccept: '*/*',
        hasOriginalAttachment: !!post.attachment,
        originalAttachmentName: post.attachment ? post.attachment.name : '',
        originalAttachmentType: post.attachment ? post.attachment.type : '',
        originalAttachmentPath: post.attachment ? post.attachment.path : ''
    };
};

const cancelEditPost = () => {
    editingPostId.value = null;
};

const editFileInput = ref<HTMLInputElement | null>(null);

const selectEditAttachmentType = (type: 'image' | 'video' | 'audio' | 'file' | 'link') => {
    editForm.value.showAttachmentDropdown = false;
    if (type === 'link') {
        editForm.value.showLinkInput = true;
        removeEditAttachment();
    } else {
        editForm.value.showLinkInput = false;
        removeEditLink();
        if (type === 'image') editForm.value.fileAccept = 'image/*';
        else if (type === 'video') editForm.value.fileAccept = 'video/*';
        else if (type === 'audio') editForm.value.fileAccept = 'audio/*';
        else editForm.value.fileAccept = '*/*';
        
        setTimeout(() => {
            editFileInput.value?.click();
        }, 100);
    }
};

const handleEditFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        editForm.value.attachment = file;
        editForm.value.attachment_name = file.name;
        editForm.value.delete_attachment = true;
        editForm.value.hasOriginalAttachment = false;
        removeEditLink();
        removeEditSticker();
        
        if (file.type.startsWith('image/')) {
            editForm.value.attachmentPreviewUrl = URL.createObjectURL(file);
        } else {
            editForm.value.attachmentPreviewUrl = null;
        }
    }
};

const removeEditAttachment = () => {
    editForm.value.attachment = null;
    editForm.value.attachment_name = '';
    editForm.value.attachmentPreviewUrl = null;
    if (editFileInput.value) editFileInput.value.value = '';
};

const removeEditLink = () => {
    editForm.value.link_url = '';
    editForm.value.link_title = '';
};

const removeEditSticker = () => {
    editForm.value.sticker_url = '';
};

const handleSelectEditPostEmoji = (emoji: string) => {
    editForm.value.content += emoji;
};

const handleSelectEditPostGif = (gif: { url: string; name: string }) => {
    removeEditAttachment();
    removeEditLink();
    editForm.value.sticker_url = gif.url;
    editForm.value.delete_attachment = true;
    editForm.value.hasOriginalAttachment = false;
    editForm.value.showPostEmojiPicker = false;
};

const handleSelectEditPostSticker = (sticker: { url: string; name: string }) => {
    removeEditAttachment();
    removeEditLink();
    editForm.value.sticker_url = sticker.url;
    editForm.value.delete_attachment = true;
    editForm.value.hasOriginalAttachment = false;
    editForm.value.showPostEmojiPicker = false;
};

const saveEditPost = (postId: number) => {
    if (!editForm.value.content.trim()) return;

    // Use router.post with _method: 'PUT' to support file uploads in Laravel
    const payload: any = {
        _method: 'PUT',
        content: editForm.value.content,
        delete_attachment: editForm.value.delete_attachment ? 1 : 0
    };

    if (editForm.value.attachment) {
        payload.attachment = editForm.value.attachment;
    }
    if (editForm.value.link_url) {
        payload.link_url = editForm.value.link_url;
        payload.link_title = editForm.value.link_title;
    }
    if (editForm.value.sticker_url) {
        payload.sticker_url = editForm.value.sticker_url;
    }

    router.post(`/posts/${postId}`, payload, {
        preserveScroll: true,
        onSuccess: () => {
            editingPostId.value = null;
        }
    });
};

const deletePost = (postId: number) => {
    if (confirm('Yakin ingin menghapus postingan ini?')) {
        router.delete(`/posts/${postId}`, {
            preserveScroll: true
        });
    }
};

// Edit/Delete comment state
const editingCommentId = ref<number | null>(null);
const editingCommentContent = ref('');

const startEditComment = (comment: any) => {
    editingCommentId.value = comment.id;
    editingCommentContent.value = comment.content;
};

const cancelEditComment = () => {
    editingCommentId.value = null;
    editingCommentContent.value = '';
};

const saveEditComment = (commentId: number) => {
    if (!editingCommentContent.value.trim()) return;
    router.put(`/comments/${commentId}`, { content: editingCommentContent.value }, {
        preserveScroll: true,
        onSuccess: () => {
            editingCommentId.value = null;
            editingCommentContent.value = '';
        }
    });
};

const deleteComment = (commentId: number) => {
    if (confirm('Yakin ingin menghapus komentar ini?')) {
        router.delete(`/comments/${commentId}`, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="4C - Cozy Corner" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- 2-Column Layout (Feed + Right Sidebar) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">
            <!-- Center Feed Column -->
            <div class="col-span-1 lg:col-span-8 flex flex-col gap-stack-lg pb-12">
                <!-- Create Broadcast Card -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 shadow-sm" style="box-shadow: 0 1px 3px 0 rgba(0,0,0,0.05);">
                    <div class="flex gap-4 items-start">
                        <div class="w-10 h-10 rounded-full overflow-hidden border border-outline-variant flex items-center justify-center bg-primary-container text-on-primary font-bold text-sm shrink-0">
                            <span class="text-on-primary-container">{{ getInitials(user.name) }}</span>
                        </div>
                        <div class="flex-1 flex flex-col gap-3">
                            <textarea ref="postTextarea" v-model="form.content" class="w-full bg-transparent border-none resize-none font-body-lg text-body-lg text-on-surface placeholder-outline focus:ring-0 p-0 h-12 focus:outline-none" placeholder="Share an update, broadcast an announcement, or ask a question..." rows="2"></textarea>
                            
                            <!-- Link Input Fields -->
                            <div v-if="showLinkInput" class="flex flex-col gap-2 bg-surface-container-low rounded-xl p-3 border border-outline-variant">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-primary flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">link</span>
                                        Tambahkan Link
                                    </span>
                                    <button @click="cancelLinkInput" type="button" class="text-on-surface-variant hover:text-error bg-transparent border-none cursor-pointer p-0.5 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[16px]">close</span>
                                    </button>
                                </div>
                                <input v-model="form.link_url" type="text" placeholder="Masukkan URL (misal: https://example.com)" class="w-full bg-white dark:bg-slate-950 border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary rounded-lg py-1.5 px-3 text-xs text-on-surface focus:outline-none" />
                                <input v-model="form.link_title" type="text" placeholder="Masukkan Judul Link (opsional)" class="w-full bg-white dark:bg-slate-950 border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary rounded-lg py-1.5 px-3 text-xs text-on-surface focus:outline-none" />
                            </div>

                            <!-- Attachment preview -->
                            <div v-if="form.attachment" class="flex items-center justify-between bg-surface-container-low rounded-lg p-2 border border-outline-variant">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <img v-if="attachmentPreviewUrl" :src="attachmentPreviewUrl" class="w-10 h-10 object-cover rounded" />
                                    <span v-else class="material-symbols-outlined text-primary text-[20px]">description</span>
                                    <span class="text-xs text-on-surface truncate">{{ attachmentName }}</span>
                                </div>
                                <button @click="removeAttachment" type="button" class="text-on-surface-variant hover:text-error bg-transparent border-none cursor-pointer p-1">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            </div>

                            <!-- Link preview -->
                            <div v-if="form.link_url && !showLinkInput" class="flex items-center justify-between bg-surface-container-low rounded-lg p-2 border border-outline-variant">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[18px]">link</span>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-xs font-bold text-on-surface truncate">{{ form.link_title || form.link_url }}</span>
                                        <span class="text-[10px] text-primary truncate">{{ form.link_url }}</span>
                                    </div>
                                </div>
                                <button @click="removeLink" type="button" class="text-on-surface-variant hover:text-error bg-transparent border-none cursor-pointer p-1">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            </div>

                            <!-- Sticker / GIF preview -->
                            <div v-if="form.sticker_url" class="flex items-center justify-between bg-surface-container-low rounded-lg p-2 border border-outline-variant">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <img :src="form.sticker_url" class="w-10 h-10 object-contain rounded" />
                                    <span class="text-xs text-on-surface truncate">Sticker / GIF Terlampir</span>
                                </div>
                                <button @click="removeSticker" type="button" class="text-on-surface-variant hover:text-error bg-transparent border-none cursor-pointer p-1">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-outline-variant relative">
                                <div class="flex items-center gap-1 select-none">
                                    <!-- Hidden File Input -->
                                    <input type="file" ref="fileInput" :accept="fileAccept" @change="handleFileChange" class="hidden" />
                                    <!-- Hidden File Input for Editing -->
                                    <input type="file" ref="editFileInput" :accept="editForm.fileAccept" @change="handleEditFileChange" class="hidden" />

                                    <!-- Attachment Dropdown -->
                                    <div ref="attachmentContainer" class="relative">
                                        <button @click="showAttachmentDropdown = !showAttachmentDropdown; if (showAttachmentDropdown) showPostEmojiPicker = false;" type="button" class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-low rounded-full transition-colors flex items-center justify-center cursor-pointer border-none bg-transparent" title="Tambah Lampiran">
                                            <span class="material-symbols-outlined text-[20px]">attach_file</span>
                                        </button>
                                        
                                        <div v-if="showAttachmentDropdown" class="absolute left-0 top-11 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-2 z-50 flex flex-col">
                                            <button @click="selectAttachmentType('image')" type="button" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent text-left text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer w-full">
                                                <span class="material-symbols-outlined text-[18px] text-blue-500">image</span>
                                                Gambar
                                            </button>
                                            <button @click="selectAttachmentType('video')" type="button" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent text-left text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer w-full">
                                                <span class="material-symbols-outlined text-[18px] text-red-500">movie</span>
                                                Video
                                            </button>
                                            <button @click="selectAttachmentType('audio')" type="button" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent text-left text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer w-full">
                                                <span class="material-symbols-outlined text-[18px] text-emerald-500">audiotrack</span>
                                                Musik / Audio
                                            </button>
                                            <button @click="selectAttachmentType('file')" type="button" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent text-left text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer w-full">
                                                <span class="material-symbols-outlined text-[18px] text-amber-500">folder_zip</span>
                                                Dokumen / Compress
                                            </button>
                                            <button @click="selectAttachmentType('link')" type="button" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent text-left text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer w-full border-t border-slate-100 dark:border-slate-850">
                                                <span class="material-symbols-outlined text-[18px] text-indigo-500">link</span>
                                                Tautan / Link
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Emoji & Sticker Picker -->
                                    <div ref="emojiPickerContainer" class="relative">
                                        <button @click="showPostEmojiPicker = !showPostEmojiPicker; if (showPostEmojiPicker) showAttachmentDropdown = false;" type="button" class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-low rounded-full transition-colors flex items-center justify-center cursor-pointer border-none bg-transparent" title="Pilih Emoji/Stiker">
                                            <span class="material-symbols-outlined text-[20px]">sentiment_satisfied</span>
                                        </button>
                                        
                                        <div v-if="showPostEmojiPicker" class="absolute left-0 top-11 z-50">
                                            <EmojiStickerPicker 
                                                @select-emoji="handleSelectPostEmoji"
                                                @select-gif="handleSelectPostGif"
                                                @select-sticker="handleSelectPostSticker"
                                            />
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <!-- Channel Selector (only when not in specific channel view) -->
                                    <select v-if="!activeChannel" v-model="form.channel_id" class="text-xs bg-surface-container border border-outline rounded px-2 py-1 text-on-surface focus:outline-none max-w-[150px]">
                                        <option value="">General Feed</option>
                                        <option v-for="ch in channels" :key="ch.id" :value="ch.id">{{ ch.name }}</option>
                                    </select>
                                    <span v-else class="text-xs bg-secondary-container text-on-secondary-container px-2.5 py-1 rounded-full font-medium">
                                        Posting to: {{ activeChannel.name }}
                                    </span>

                                    <button @click="handleCreatePost" class="bg-primary hover:bg-on-primary-fixed-variant text-on-primary font-label-md text-label-md px-5 py-2 rounded-lg transition-colors shadow-sm cursor-pointer border-none">
                                        Post
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feed Divider -->
                <div class="flex items-center gap-4 py-2">
                    <div class="h-px bg-outline-variant flex-1"></div>
                    <span class="font-label-sm text-label-sm text-outline uppercase tracking-widest select-none">
                        {{ activeChannel ? `Recent Updates in ${activeChannel.name}` : 'Recent Updates' }}
                    </span>
                    <div class="h-px bg-outline-variant flex-1"></div>
                </div>

                <!-- Feed Posts -->
                <article v-for="post in posts" :key="post.id" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm flex flex-col gap-4">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <Link :href="`/profile/${post.author_id}`" class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-sm shrink-0 border border-outline-variant overflow-hidden hover:opacity-90 transition-opacity">
                                <img v-if="post.author.avatar" :src="post.author.avatar" class="w-full h-full object-cover" />
                                <span v-else>{{ getInitials(post.author.name) }}</span>
                            </Link>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <Link :href="`/profile/${post.author_id}`" class="font-label-md text-label-md text-on-surface hover:text-primary transition-colors font-semibold no-underline">{{ post.author.name }}</Link>
                                    <span v-if="post.author.role" class="font-label-sm text-label-sm px-2 py-0.5 rounded-full font-medium bg-surface-variant text-on-surface-variant">
                                        {{ post.author.role }}
                                    </span>
                                </div>
                                <span class="font-label-sm text-label-sm text-on-surface-variant select-none">{{ post.time }} • {{ post.category }}</span>
                            </div>
                        </div>

                        <!-- Edit / Delete actions for post owner -->
                        <div v-if="post.author_id === user.id || hasRole('Super Admin')" class="flex items-center gap-1">
                            <button @click="startEditPost(post)" class="text-on-surface-variant hover:text-primary p-1.5 rounded hover:bg-surface-container-low transition-colors cursor-pointer border-none bg-transparent flex items-center justify-center" title="Edit">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                            </button>
                            <button @click="deletePost(post.id)" class="text-on-surface-variant hover:text-error p-1.5 rounded hover:bg-surface-container-low transition-colors cursor-pointer border-none bg-transparent flex items-center justify-center" title="Hapus">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Body -->
                    <div v-if="editingPostId === post.id" class="flex flex-col gap-3 bg-surface-container-lowest border border-outline-variant p-4 rounded-xl shadow-xs">
                        <textarea v-model="editForm.content" class="w-full bg-surface-container-low border border-outline-variant rounded-lg p-3 font-body-lg text-body-lg text-on-surface focus:outline-none focus:ring-1 focus:ring-primary" rows="3" placeholder="Apa yang ingin Anda bagikan?"></textarea>

                        <!-- Link Inputs in Edit Form -->
                        <div v-if="editForm.showLinkInput" class="flex flex-col gap-2 p-3 bg-surface-container-low border border-outline-variant rounded-lg">
                            <div class="flex items-center justify-between mb-1 select-none">
                                <span class="text-xs font-semibold text-on-surface">Lampirkan Tautan / Link</span>
                                <button @click="editForm.showLinkInput = false; removeEditLink()" type="button" class="text-on-surface-variant hover:text-error bg-transparent border-none cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            </div>
                            <input v-model="editForm.link_url" type="text" placeholder="Masukkan URL (misal: https://example.com)" class="w-full bg-white dark:bg-slate-950 border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary rounded-lg py-1.5 px-3 text-xs text-on-surface focus:outline-none" />
                            <input v-model="editForm.link_title" type="text" placeholder="Masukkan Judul Link (opsional)" class="w-full bg-white dark:bg-slate-950 border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary rounded-lg py-1.5 px-3 text-xs text-on-surface focus:outline-none" />
                        </div>

                        <!-- Original Attachment Preview in Edit Form -->
                        <div v-if="editForm.hasOriginalAttachment" class="flex items-center justify-between bg-surface-container-low rounded-lg p-2.5 border border-outline-variant">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <img v-if="editForm.originalAttachmentType === 'image'" :src="editForm.originalAttachmentPath" class="w-10 h-10 object-cover rounded" />
                                <span v-else class="material-symbols-outlined text-primary text-[20px]">description</span>
                                <span class="text-xs text-on-surface truncate">{{ editForm.originalAttachmentName }}</span>
                            </div>
                            <button @click="editForm.hasOriginalAttachment = false; editForm.delete_attachment = true" type="button" class="text-on-surface-variant hover:text-error bg-transparent border-none cursor-pointer p-1" title="Hapus Lampiran">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                        </div>

                        <!-- New File Attachment preview in Edit Form -->
                        <div v-if="editForm.attachment" class="flex items-center justify-between bg-surface-container-low rounded-lg p-2 border border-outline-variant">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <img v-if="editForm.attachmentPreviewUrl" :src="editForm.attachmentPreviewUrl" class="w-10 h-10 object-cover rounded" />
                                <span v-else class="material-symbols-outlined text-primary text-[20px]">description</span>
                                <span class="text-xs text-on-surface truncate">{{ editForm.attachment_name }}</span>
                            </div>
                            <button @click="removeEditAttachment" type="button" class="text-on-surface-variant hover:text-error bg-transparent border-none cursor-pointer p-1">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                        </div>

                        <!-- New Link Preview in Edit Form -->
                        <div v-if="editForm.link_url && !editForm.showLinkInput" class="flex items-center justify-between bg-surface-container-low rounded-lg p-2 border border-outline-variant">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">link</span>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-xs font-bold text-on-surface truncate">{{ editForm.link_title || editForm.link_url }}</span>
                                    <span class="text-[10px] text-primary truncate">{{ editForm.link_url }}</span>
                                </div>
                            </div>
                            <button @click="removeEditLink" type="button" class="text-on-surface-variant hover:text-error bg-transparent border-none cursor-pointer p-1">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                        </div>

                        <!-- New Sticker Preview in Edit Form -->
                        <div v-if="editForm.sticker_url" class="flex items-center justify-between bg-surface-container-low rounded-lg p-2 border border-outline-variant">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <img :src="editForm.sticker_url" class="w-10 h-10 object-contain rounded" />
                                <span class="text-xs text-on-surface truncate">Sticker / GIF Terlampir</span>
                            </div>
                            <button @click="removeEditSticker" type="button" class="text-on-surface-variant hover:text-error bg-transparent border-none cursor-pointer p-1">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                        </div>

                        <div class="flex items-center justify-between pt-1 relative">
                            <div class="flex items-center gap-1 select-none">
                                <!-- Edit Form Attachment Dropdown -->
                                <div class="relative edit-attachment-container">
                                    <button @click="editForm.showAttachmentDropdown = !editForm.showAttachmentDropdown; if (editForm.showAttachmentDropdown) editForm.showPostEmojiPicker = false;" type="button" class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-low rounded-full transition-colors flex items-center justify-center cursor-pointer border-none bg-transparent" title="Tambah/Ganti Lampiran">
                                        <span class="material-symbols-outlined text-[20px]">attach_file</span>
                                    </button>
                                    
                                    <div v-if="editForm.showAttachmentDropdown" class="absolute left-0 top-11 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-2 z-50 flex flex-col">
                                        <button @click="selectEditAttachmentType('image')" type="button" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent text-left text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer w-full">
                                            <span class="material-symbols-outlined text-[18px] text-blue-500">image</span>
                                            Gambar
                                        </button>
                                        <button @click="selectEditAttachmentType('video')" type="button" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent text-left text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer w-full">
                                            <span class="material-symbols-outlined text-[18px] text-red-500">movie</span>
                                            Video
                                        </button>
                                        <button @click="selectEditAttachmentType('audio')" type="button" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent text-left text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer w-full">
                                            <span class="material-symbols-outlined text-[18px] text-emerald-500">audiotrack</span>
                                            Musik / Audio
                                        </button>
                                        <button @click="selectEditAttachmentType('file')" type="button" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent text-left text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer w-full">
                                            <span class="material-symbols-outlined text-[18px] text-amber-500">folder_zip</span>
                                            Dokumen / Compress
                                        </button>
                                        <button @click="selectEditAttachmentType('link')" type="button" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 border-none bg-transparent text-left text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer w-full border-t border-slate-100 dark:border-slate-850">
                                            <span class="material-symbols-outlined text-[18px] text-indigo-500">link</span>
                                            Tautan / Link
                                        </button>
                                    </div>
                                </div>

                                <!-- Edit Form Emoji Picker -->
                                <div class="relative edit-emoji-picker-container">
                                    <button @click="editForm.showPostEmojiPicker = !editForm.showPostEmojiPicker; if (editForm.showPostEmojiPicker) editForm.showAttachmentDropdown = false;" type="button" class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-low rounded-full transition-colors flex items-center justify-center cursor-pointer border-none bg-transparent" title="Pilih Emoji/Stiker">
                                        <span class="material-symbols-outlined text-[20px]">sentiment_satisfied</span>
                                    </button>
                                    
                                    <div v-if="editForm.showPostEmojiPicker" class="absolute left-0 top-11 z-50">
                                        <EmojiStickerPicker 
                                            @select-emoji="handleSelectEditPostEmoji"
                                            @select-gif="handleSelectEditPostGif"
                                            @select-sticker="handleSelectEditPostSticker"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button @click="cancelEditPost" class="bg-surface-container border border-outline text-on-surface font-label-md px-3 py-1.5 rounded-lg cursor-pointer transition-colors text-xs">
                                    Cancel
                                </button>
                                <button @click="saveEditPost(post.id)" class="bg-primary hover:bg-on-primary-fixed-variant text-on-primary font-label-md px-4 py-1.5 rounded-lg cursor-pointer transition-colors text-xs border-none">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="font-body-lg text-body-lg text-on-surface leading-relaxed whitespace-pre-wrap" v-html="linkify(post.content)"></div>
                    
                    <!-- Attachment -->
                    <div v-if="post.attachment" class="mt-2">
                        <!-- Image Attachment -->
                        <div v-if="post.attachment.type === 'image'" class="mb-2">
                            <a :href="post.attachment.path" target="_blank">
                                <img :src="post.attachment.path" alt="Image attachment" class="rounded-lg max-h-[400px] w-auto object-contain border border-outline-variant hover:opacity-95 transition-opacity" />
                            </a>
                        </div>
                        
                        <!-- Video Attachment -->
                        <div v-else-if="post.attachment.type === 'video'" class="mb-2">
                            <video :src="post.attachment.path" controls class="rounded-lg max-h-[400px] w-full bg-black border border-outline-variant"></video>
                        </div>

                        <!-- Audio Attachment -->
                        <div v-else-if="post.attachment.type === 'audio'" class="mb-2">
                            <audio :src="post.attachment.path" controls class="w-full bg-surface-container-low rounded-lg p-1 border border-outline-variant"></audio>
                        </div>

                        <!-- Link Attachment -->
                        <div v-else-if="post.attachment.type === 'link'" class="mb-2">
                            <a :href="post.attachment.path" target="_blank" class="border border-outline-variant rounded-xl p-4 flex items-center gap-4 bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer text-inherit block no-underline shadow-sm hover:shadow-md">
                                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[24px]">link</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-label-lg text-label-lg text-on-surface truncate font-bold">{{ post.attachment.name }}</p>
                                    <p class="font-label-sm text-label-sm text-primary hover:underline truncate select-none mt-0.5">{{ post.attachment.path }}</p>
                                </div>
                                <div class="text-on-surface-variant flex items-center justify-center">
                                    <span class="material-symbols-outlined">open_in_new</span>
                                </div>
                            </a>
                        </div>
                        
                        <!-- General File Card -->
                        <a v-else :href="post.attachment.path" download class="border border-outline-variant rounded-lg p-3 flex items-center gap-4 bg-surface-container-low hover:bg-surface-container-high transition-colors cursor-pointer text-inherit block no-underline">
                            <div class="w-12 h-12 bg-primary-container rounded flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-primary text-[24px]" style="font-variation-settings: 'FILL' 1;">description</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-label-md text-label-md text-on-surface truncate font-semibold">{{ post.attachment.name }}</p>
                                <p class="font-label-sm text-label-sm text-on-surface-variant select-none">{{ post.attachment.size }} • Download</p>
                            </div>
                            <div class="text-on-surface-variant hover:text-primary flex items-center justify-center">
                                <span class="material-symbols-outlined">download</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Footer / Engagement -->
                    <div class="pt-3 border-t border-outline-variant flex items-center gap-6 mt-1">
                        <button @click="toggleReaction(post.id, 'like')" :class="['flex items-center gap-1.5 transition-colors group cursor-pointer border-none bg-transparent', post.liked ? 'text-primary font-bold animate-pulse' : 'text-on-surface-variant hover:text-primary']">
                            <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform" :style="post.liked ? 'font-variation-settings: \'FILL\' 1;' : ''">thumb_up</span>
                            <span class="font-label-md text-label-md">{{ post.likes }}</span>
                        </button>
                        <button @click="toggleReaction(post.id, 'bulb')" :class="['flex items-center gap-1.5 transition-colors group cursor-pointer border-none bg-transparent', post.bulbed ? 'text-amber-500 font-bold animate-pulse' : 'text-on-surface-variant hover:text-primary']">
                            <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform" :style="post.bulbed ? 'font-variation-settings: \'FILL\' 1;' : ''">lightbulb</span>
                            <span class="font-label-md text-label-md">{{ post.bulbs }}</span>
                        </button>
                        <button @click="toggleCommentForm(post.id)" class="flex items-center gap-1.5 text-on-surface-variant hover:text-primary transition-colors group ml-auto cursor-pointer border-none bg-transparent">
                            <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">chat_bubble</span>
                            <span class="font-label-md text-label-md">{{ post.comments.length ? `${post.comments.length} Comments` : 'Comment' }}</span>
                        </button>
                    </div>

                    <!-- Nested Comment Thread -->
                    <div v-if="post.comments.length > 0 || activeCommentFormPostId[post.id]" class="mt-2 pl-4 border-l-2 border-surface-variant flex flex-col gap-4">
                        <!-- Comment List -->
                        <div v-for="comment in post.comments" :key="comment.id" class="flex gap-3">
                            <Link :href="`/profile/${comment.author_id}`" class="w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-xs shrink-0 border border-outline-variant overflow-hidden hover:opacity-90 transition-opacity">
                                <img v-if="comment.author.avatar" :src="comment.author.avatar" class="w-full h-full object-cover" />
                                <span v-else>{{ getInitials(comment.author.name) }}</span>
                            </Link>
                            <div class="flex-1 bg-surface-container-low rounded-lg p-3">
                                <div class="flex items-baseline justify-between mb-1">
                                    <Link :href="`/profile/${comment.author_id}`" class="font-label-md text-label-md text-on-surface hover:text-primary transition-colors font-semibold no-underline">{{ comment.author.name }}</Link>
                                    <div class="flex items-center gap-2">
                                        <span class="font-label-sm text-label-sm text-on-surface-variant select-none mr-2">{{ comment.time }}</span>
                                        <button v-if="comment.author_id === user.id || hasRole('Super Admin')" @click="startEditComment(comment)" class="text-on-surface-variant hover:text-primary transition-colors cursor-pointer border-none bg-transparent p-0 flex items-center justify-center" title="Edit">
                                            <span class="material-symbols-outlined text-[14px]">edit</span>
                                        </button>
                                        <button v-if="comment.author_id === user.id || post.author_id === user.id || hasRole('Super Admin')" @click="deleteComment(comment.id)" class="text-on-surface-variant hover:text-error transition-colors cursor-pointer border-none bg-transparent p-0 flex items-center justify-center" title="Hapus">
                                            <span class="material-symbols-outlined text-[14px]">delete</span>
                                        </button>
                                    </div>
                                </div>
                                
                                <div v-if="editingCommentId === comment.id" class="flex gap-2 mt-1">
                                    <input v-model="editingCommentContent" @keyup.enter="saveEditComment(comment.id)" type="text" class="flex-1 bg-surface-container-lowest border border-outline focus:border-primary rounded-lg px-2 py-1 text-xs text-on-surface focus:outline-none" />
                                    <button @click="cancelEditComment" class="bg-surface-container border border-outline text-on-surface font-label-sm px-2 py-1 rounded-md text-[10px] cursor-pointer">Cancel</button>
                                    <button @click="saveEditComment(comment.id)" class="bg-primary text-on-primary font-label-sm px-3 py-1 rounded-md text-[10px] border-none cursor-pointer">Save</button>
                                </div>
                                <div v-else class="flex flex-col gap-2">
                                    <p class="font-body-md text-body-md text-on-surface leading-relaxed whitespace-pre-wrap" v-html="linkify(parseComment(comment.content).text)"></p>
                                    
                                    <div v-if="parseComment(comment.content).mediaUrl" class="mt-1 select-none">
                                        <img :src="parseComment(comment.content).mediaUrl" :alt="parseComment(comment.content).mediaType" 
                                             :class="[
                                                 parseComment(comment.content).mediaType === 'sticker' ? 'w-24 h-24 object-contain' : 'rounded-lg max-h-[160px] w-auto object-contain border border-slate-200 dark:border-slate-800'
                                             ]" />
                                    </div>
                                    
                                    <div v-if="parseComment(comment.content).firstUrl" class="mt-1">
                                        <a :href="parseComment(comment.content).firstUrl" target="_blank" class="border border-slate-200 dark:border-slate-800 rounded-lg p-2.5 flex items-center gap-3 bg-white dark:bg-slate-950 hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors cursor-pointer text-inherit no-underline shadow-xs max-w-md">
                                            <span class="material-symbols-outlined text-[18px] text-indigo-500 shrink-0">link</span>
                                            <span class="text-xs text-primary truncate hover:underline leading-none select-none font-semibold">{{ parseComment(comment.content).firstUrl }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comment Input Form -->
                        <div class="flex gap-3 mt-1 relative">
                            <div class="w-8 h-8 rounded-full bg-primary-container text-on-primary flex items-center justify-center font-bold text-xs shrink-0 border border-outline-variant overflow-hidden">
                                <img v-if="user.avatar_url" :src="user.avatar_url" class="w-full h-full object-cover" />
                                <span v-else>{{ getInitials(user.name) }}</span>
                            </div>
                            <div class="flex-1 flex gap-2 items-center relative">
                                <input v-model="commentInputs[post.id]" @keyup.enter="handleAddComment(post.id)" type="text" class="flex-1 bg-surface-container-low border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary rounded-lg py-1.5 px-3 font-body-md text-body-md text-on-surface placeholder-outline focus:outline-none transition-all" placeholder="Write a comment..." />
                                
                                <!-- Comment Emoji Picker Trigger -->
                                <div class="relative shrink-0 select-none comment-emoji-picker-container">
                                    <button @click="toggleCommentEmojiPicker(post.id)" type="button" class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-low rounded-full transition-colors flex items-center justify-center cursor-pointer border-none bg-transparent" title="Pilih Emoji/Stiker">
                                        <span class="material-symbols-outlined text-[20px]">sentiment_satisfied</span>
                                    </button>
                                    
                                    <div v-if="activeCommentEmojiPickerPostId === post.id" class="absolute right-0 top-11 z-50">
                                        <EmojiStickerPicker 
                                            @select-emoji="(emoji) => handleSelectCommentEmoji(post.id, emoji)"
                                            @select-gif="(gif) => handleSelectCommentGif(post.id, gif)"
                                            @select-sticker="(sticker) => handleSelectCommentSticker(post.id, sticker)"
                                        />
                                    </div>
                                </div>

                                <button @click="handleAddComment(post.id)" class="bg-primary hover:bg-on-primary-fixed-variant text-on-primary font-label-md text-label-md px-4 py-1.5 rounded-lg transition-colors cursor-pointer border-none shrink-0">Send</button>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Fallback when no posts -->
                <div v-if="posts.length === 0" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-8 text-center text-on-surface-variant shadow-sm">
                    <span class="material-symbols-outlined text-[48px] text-outline mb-2">rss_feed</span>
                    <p class="font-body-lg text-body-lg">No posts yet in this channel.</p>
                    <p class="font-label-sm text-label-sm text-outline">Be the first to share an update!</p>
                </div>
            </div>

            <!-- Right Sidebar: Directory & Quick Stats -->
            <div class="hidden lg:flex col-span-4 flex-col gap-stack-lg">
                <!-- Directory Widget -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4 select-none">
                        <h3 class="font-headline-md text-headline-md text-on-surface">List User Online</h3>
                    </div>
                    <div class="relative mb-4">
                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                        <input v-model="searchColleagueQuery" class="w-full bg-surface-container-low border border-outline-variant focus:border-primary rounded-lg py-1.5 pl-9 pr-3 font-body-md text-body-md text-on-surface placeholder-outline focus:outline-none transition-all" placeholder="Find colleagues..." type="text"/>
                    </div>
                    <div class="flex flex-col gap-3">
                        <div v-for="colleague in filteredColleagues" :key="colleague.id" class="flex items-center gap-3 group p-1 -mx-1 rounded-lg transition-colors">
                            <Link :href="`/profile/${colleague.id}`" class="relative shrink-0 block hover:opacity-90 transition-opacity">
                                <div class="w-9 h-9 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-xs shrink-0 border border-outline-variant overflow-hidden">
                                    <img v-if="colleague.avatar" :src="colleague.avatar" class="w-full h-full object-cover" />
                                    <span v-else>{{ getInitials(colleague.name) }}</span>
                                </div>
                                <div :class="['absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-surface-container-lowest', colleague.status === 'online' ? 'bg-green-500' : 'bg-amber-400']"></div>
                            </Link>
                            <div class="flex-1 min-w-0">
                                <Link :href="`/profile/${colleague.id}`" class="font-label-md text-label-md text-on-surface truncate font-semibold hover:text-primary transition-colors block no-underline">{{ colleague.name }}</Link>
                                <p class="font-label-sm text-label-sm text-on-surface-variant truncate">{{ colleague.role }}</p>
                            </div>
                        </div>
                        <div v-if="filteredColleagues.length === 0" class="text-center py-4 text-on-surface-variant text-xs select-none">
                            No active colleagues online
                        </div>
                    </div>
                </div>

                <!-- Trending Topics / Information Widget (Dynamic Corporate Highlights) -->
                <div class="bg-surface-container-low border border-outline-variant rounded-xl p-5">
                    <h3 class="font-label-md text-label-md text-outline uppercase tracking-wider mb-4 select-none">Papan Pengumuman</h3>
                    <ul class="flex flex-col gap-3">
                        <li v-for="highlight in highlights" :key="highlight.id">
                            <a v-if="highlight.link && highlight.link !== '#'" :href="highlight.link" target="_blank" class="group block no-underline text-inherit">
                                <p class="font-label-md text-label-md text-on-surface font-semibold group-hover:text-primary transition-colors">{{ highlight.title }}</p>
                                <p class="font-label-sm text-label-sm text-on-surface-variant select-none">{{ highlight.description }}</p>
                            </a>
                            <div v-else class="block">
                                <p class="font-label-md text-label-md text-on-surface font-semibold">{{ highlight.title }}</p>
                                <p class="font-label-sm text-label-sm text-on-surface-variant select-none">{{ highlight.description }}</p>
                            </div>
                        </li>
                        <li v-if="highlights.length === 0" class="text-center py-4 text-on-surface-variant text-xs select-none">
                            No corporate highlights available.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
