<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const userRoles = computed(() => page.props.auth?.user?.role || []);

const hasRole = (role: string) => {
    if (Array.isArray(userRoles.value)) {
        return userRoles.value.includes(role);
    }
    return userRoles.value === role;
};

// Collapsible Sidebar state (Desktop)
const isSidebarCollapsed = ref(false);

onMounted(() => {
    isSidebarCollapsed.value = localStorage.getItem('sidebar_collapsed') === 'true';
});

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    localStorage.setItem('sidebar_collapsed', isSidebarCollapsed.value ? 'true' : 'false');
};

// Mobile Sidebar state
const showMobileSidebar = ref(false);

// User Profile Menu Dropdown state
const showUserMenu = ref(false);

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

// Active state detection
const currentUrl = computed(() => page.url);
const isActive = (path: string) => {
    if (path === '/dashboard') {
        return currentUrl.value === '/dashboard' || currentUrl.value === '/';
    }
    return currentUrl.value.startsWith(path);
};

// "Create Post" global action
const handleCreatePostClick = () => {
    if (currentUrl.value === '/dashboard') {
        // If on dashboard, trigger the focus event via custom window event
        window.dispatchEvent(new CustomEvent('focus-post-textarea'));
    } else {
        // Otherwise, redirect to dashboard with query param
        router.visit('/dashboard?create=true');
    }
};

import axios from 'axios';
import { onBeforeUnmount } from 'vue';

// DM Float Widget State
const showDmWidget = ref(false);
const dmContacts = ref<any[]>([]);
const activeDmChat = ref<any | null>(null);
const dmMessages = ref<any[]>([]);
const newDmMessage = ref('');
const messagesContainer = ref<HTMLDivElement | null>(null);
let historyPollInterval: any = null;

const toggleDmWidget = () => {
    showDmWidget.value = !showDmWidget.value;
    if (showDmWidget.value) {
        fetchDmContacts();
    } else {
        closeActiveChat();
    }
};

const fetchDmContacts = async () => {
    try {
        const res = await axios.get('/api/direct-messages/contacts');
        dmContacts.value = res.data;
    } catch (err) {
        console.error('Failed to fetch DM contacts', err);
    }
};

const openChat = async (contact: any) => {
    activeDmChat.value = contact;
    newDmMessage.value = '';
    await fetchChatHistory(contact.id);
    
    // Poll for new messages every 3 seconds while chat is active
    if (historyPollInterval) clearInterval(historyPollInterval);
    historyPollInterval = setInterval(() => {
        if (activeDmChat.value) {
            fetchChatHistory(activeDmChat.value.id, true);
        }
    }, 3000);

    // Scroll to bottom
    setTimeout(scrollToBottom, 100);
};

const closeActiveChat = () => {
    activeDmChat.value = null;
    dmMessages.value = [];
    if (historyPollInterval) {
        clearInterval(historyPollInterval);
        historyPollInterval = null;
    }
    // Refresh contacts list to clear unread counts
    fetchDmContacts();
};

const fetchChatHistory = async (userId: number, quiet = false) => {
    try {
        const res = await axios.get(`/api/direct-messages/history/${userId}`);
        const oldLength = dmMessages.value.length;
        dmMessages.value = res.data;
        if (dmMessages.value.length > oldLength) {
            setTimeout(scrollToBottom, 50);
        }
    } catch (err) {
        console.error('Failed to fetch chat history', err);
    }
};

const sendDmMessage = async () => {
    if (!newDmMessage.value.trim() || !activeDmChat.value) return;
    
    const text = newDmMessage.value;
    newDmMessage.value = '';
    
    try {
        const res = await axios.post('/api/direct-messages/send', {
            receiver_id: activeDmChat.value.id,
            message: text
        });
        if (res.data.success) {
            dmMessages.value.push(res.data.message);
            setTimeout(scrollToBottom, 50);
        }
    } catch (err) {
        console.error('Failed to send DM message', err);
        newDmMessage.value = text;
    }
};

const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

onBeforeUnmount(() => {
    if (historyPollInterval) clearInterval(historyPollInterval);
});
</script>

<template>
    <Head>
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    </Head>

    <div class="bg-surface text-on-surface min-h-screen flex font-body-md text-body-md w-full relative" style="font-family: 'Inter', sans-serif;">
        <!-- Backdrop overlay for mobile sidebar -->
        <div v-if="showMobileSidebar" @click="showMobileSidebar = false" class="fixed inset-0 bg-black/40 z-30 lg:hidden"></div>

        <!-- SideNavBar -->
        <aside :class="[
            'bg-surface-container-low dark:bg-surface-container-highest fixed left-0 top-0 h-full border-r border-outline-variant dark:border-outline flex flex-col py-6 gap-4 z-40 overflow-y-auto transition-all duration-300 lg:translate-x-0',
            isSidebarCollapsed ? 'w-16 px-2' : 'w-60 px-4',
            showMobileSidebar ? 'translate-x-0' : '-translate-x-full'
        ]">
            <!-- Header -->
            <div class="flex items-center gap-3 px-2 mb-2 overflow-hidden select-none shrink-0" :class="isSidebarCollapsed ? 'justify-center' : ''">
                <div class="w-10 h-10 rounded-lg bg-primary-container flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-on-primary-container" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                </div>
                <div v-if="!isSidebarCollapsed" class="min-w-0 transition-all duration-200">
                    <h2 class="text-headline-md font-display font-bold text-primary dark:text-inverse-primary truncate">C4</h2>
                    <p class="font-label-sm text-label-sm text-on-surface-variant truncate">Cozy Corner Club</p>
                </div>
            </div>

            <!-- CTA -->
            <button @click="handleCreatePostClick" :class="[
                'bg-primary hover:bg-on-primary-fixed-variant text-on-primary font-label-md text-label-md py-3 rounded-lg shadow-sm transition-all duration-150 mb-2 flex items-center justify-center gap-2 cursor-pointer border-none shrink-0',
                isSidebarCollapsed ? 'px-0 w-10 h-10 mx-auto rounded-full' : 'w-full'
            ]" :title="isSidebarCollapsed ? 'Create Post' : ''">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span v-if="!isSidebarCollapsed">Create Post</span>
            </button>

            <!-- Navigation Tabs -->
            <nav class="flex-1 flex flex-col gap-1">
                <!-- Dashboard / Home Feed -->
                <Link :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    isActive('/dashboard') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-dim'
                ]" href="/dashboard" :title="isSidebarCollapsed ? 'Home Feed' : ''">
                    <span class="material-symbols-outlined" :style="isActive('/dashboard') ? 'font-variation-settings: \'FILL\' 1;' : ''">home</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">Home Feed</span>
                </Link>
                
                <Link :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    isActive('/direct-messages') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-dim'
                ]" href="/direct-messages" :title="isSidebarCollapsed ? 'Direct Messages' : ''">
                    <span class="material-symbols-outlined" :style="isActive('/direct-messages') ? 'font-variation-settings: \'FILL\' 1;' : ''">chat</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">Direct Messages</span>
                </Link>
                
                <!-- Dynamic Navigation depending on roles -->
                <Link v-if="hasRole('Super Admin') || hasRole('Admin Kanwil')" :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    isActive('/admin/data-akun') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-dim'
                ]" href="/admin/data-akun" :title="isSidebarCollapsed ? 'Manajemen User' : ''">
                    <span class="material-symbols-outlined" :style="isActive('/admin/data-akun') ? 'font-variation-settings: \'FILL\' 1;' : ''">group</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">Manajemen User</span>
                </Link>

                <Link v-if="hasRole('Super Admin') || hasRole('Admin Kanwil')" :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    isActive('/admin/kanwils') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-dim'
                ]" href="/admin/kanwils" :title="isSidebarCollapsed ? 'Kanwil' : ''">
                    <span class="material-symbols-outlined" :style="isActive('/admin/kanwils') ? 'font-variation-settings: \'FILL\' 1;' : ''">account_balance</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">Kanwil</span>
                </Link>
                
                <Link v-if="hasRole('Super Admin') || hasRole('Admin Kanwil')" :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    isActive('/admin/upts') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-dim'
                ]" href="/admin/upts" :title="isSidebarCollapsed ? 'UPT' : ''">
                    <span class="material-symbols-outlined" :style="isActive('/admin/upts') ? 'font-variation-settings: \'FILL\' 1;' : ''">business</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">UPT</span>
                </Link>

                <!-- <a v-if="hasRole('Super Admin') || hasRole('Admin Kanwil')" :class="[
                    'flex items-center gap-3 px-3 py-2 text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-dim transition-all duration-200 rounded-lg',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : ''
                ]" href="#" :title="isSidebarCollapsed ? 'Jabatan' : ''">
                    <span class="material-symbols-outlined">assignment_ind</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">Jabatan</span>
                </a> -->

                <Link v-if="hasRole('Super Admin') || hasRole('Admin Kanwil')" :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    isActive('/admin/jenis-golongans') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-dim'
                ]" href="/admin/jenis-golongans" :title="isSidebarCollapsed ? 'Pangkat' : ''">
                    <span class="material-symbols-outlined" :style="isActive('/admin/jenis-golongans') ? 'font-variation-settings: \'FILL\' 1;' : ''">military_tech</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">Pangkat</span>
                </Link>

                <Link v-if="hasRole('Super Admin') || hasRole('Admin Kanwil')" :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    isActive('/admin/channels') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-dim'
                ]" href="/admin/channels" :title="isSidebarCollapsed ? 'Channels' : ''">
                    <span class="material-symbols-outlined" :style="isActive('/admin/channels') ? 'font-variation-settings: \'FILL\' 1;' : ''">hub</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">Channels</span>
                </Link>

                <Link v-if="hasRole('Super Admin') || hasRole('Admin Kanwil')" :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    isActive('/admin/corporate-highlights') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-dim'
                ]" href="/admin/corporate-highlights" :title="isSidebarCollapsed ? 'Highlights' : ''">
                    <span class="material-symbols-outlined" :style="isActive('/admin/corporate-highlights') ? 'font-variation-settings: \'FILL\' 1;' : ''">campaign</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">Highlights</span>
                </Link>

                <Link v-if="hasRole('Super Admin') || hasRole('Admin Kanwil')" :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200 mb-4',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    isActive('/admin/roles') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-dim'
                ]" href="/admin/roles" :title="isSidebarCollapsed ? 'Roles' : ''">
                    <span class="material-symbols-outlined" :style="isActive('/admin/roles') ? 'font-variation-settings: \'FILL\' 1;' : ''">admin_panel_settings</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">Roles</span>
                </Link>

                <!-- Prompt Categories -->
                <div class="mt-4 mb-2 px-3 border-t border-outline-variant pt-2" :class="isSidebarCollapsed ? 'mx-auto w-8 px-0' : ''">
                    <p v-if="!isSidebarCollapsed" class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Channels</p>
                </div>
                <!-- General Home Feed Link -->
                <Link :class="[
                    'flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    !$page.props.activeChannel ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-container-high'
                ]" href="/dashboard" :title="isSidebarCollapsed ? 'General Feed' : ''">
                    <span class="material-symbols-outlined text-sm">home</span>
                    <span v-if="!isSidebarCollapsed" class="font-body-md text-body-md">General Feed</span>
                </Link>
                <!-- Dynamic Channels -->
                <Link v-for="channel in ($page.props.channels as any[])" :key="channel.id" :class="[
                    'flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors mt-1',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    ($page.props.activeChannel as any)?.id === channel.id ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-container-high'
                ]" :href="`/dashboard?channel=${channel.slug}`" :title="isSidebarCollapsed ? channel.name : ''">
                    <div class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: channel.color || '#3525cd' }"></div>
                    <span v-if="!isSidebarCollapsed" class="font-body-md text-body-md truncate">{{ channel.name }}</span>
                </Link>
            </nav>

            <!-- Footer Tabs -->
            <div class="mt-auto border-t border-outline-variant pt-4 flex flex-col gap-1" :class="isSidebarCollapsed ? 'items-center px-0' : ''">
                <Link :class="[
                    'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : '',
                    isActive('/settings') ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-high'
                ]" href="/settings" :title="isSidebarCollapsed ? 'Settings' : ''">
                    <span class="material-symbols-outlined" :style="isActive('/settings') ? 'font-variation-settings: \'FILL\' 1;' : ''">settings</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">Settings</span>
                </Link>
                <a :class="[
                    'flex items-center gap-3 px-3 py-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-all duration-200',
                    isSidebarCollapsed ? 'justify-center px-0 w-10 h-10 mx-auto' : ''
                ]" href="#" :title="isSidebarCollapsed ? 'Help' : ''">
                    <span class="material-symbols-outlined">help</span>
                    <span v-if="!isSidebarCollapsed" class="font-label-md text-label-md">Help</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div :class="[
            'flex-1 flex flex-col min-w-0 overflow-y-auto transition-all duration-300',
            isSidebarCollapsed ? 'lg:ml-16' : 'lg:ml-60'
        ]">
            <!-- TopNavBar -->
            <header class="bg-surface dark:bg-inverse-surface border-b border-outline-variant dark:border-outline shadow-sm flex justify-between items-center w-full px-margin-desktop h-16 sticky top-0 z-20">
                <!-- Left: Logo & Search -->
                <div class="flex items-center gap-2 lg:gap-4">
                    <!-- Mobile Sidebar Toggle -->
                    <button @click="showMobileSidebar = !showMobileSidebar" class="lg:hidden p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-colors flex items-center justify-center cursor-pointer border-none bg-transparent">
                        <span class="material-symbols-outlined">{{ showMobileSidebar ? 'close' : 'menu' }}</span>
                    </button>
                    <!-- Desktop Sidebar Toggle -->
                    <button @click="toggleSidebar" class="hidden lg:flex p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-colors items-center justify-center cursor-pointer border-none bg-transparent" title="Toggle Sidebar">
                        <span class="material-symbols-outlined">{{ isSidebarCollapsed ? 'menu' : 'menu_open' }}</span>
                    </button>
                    <Link href="/dashboard" class="text-headline-md font-display font-bold text-primary dark:text-inverse-primary hover:opacity-90 transition-opacity no-underline scale-95 active:scale-90 select-none">C4 Cozy Corner</Link>
                    <div class="relative hidden lg:block w-96">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
                        <input class="w-full bg-surface-container-low border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary rounded-full py-2 pl-10 pr-4 font-body-md text-body-md text-on-surface placeholder-outline focus:outline-none transition-all" placeholder="Search Cozy Corner..." type="text"/>
                    </div>
                </div>

                <!-- Center: Navigation Links -->
                <nav class="hidden md:flex items-center gap-6 h-full pt-1">
                    <Link :class="[
                        'font-body-md text-body-md h-full flex items-center hover:text-primary transition-colors duration-150',
                        isActive('/dashboard') ? 'text-primary dark:text-primary-fixed border-b-2 border-primary dark:border-primary-fixed pb-1 font-medium' : 'text-on-surface-variant dark:text-surface-variant'
                    ]" href="/dashboard">Home Feed</Link>
                    <Link :class="[
                        'font-body-md text-body-md h-full flex items-center hover:text-primary transition-colors duration-150',
                        isActive('/direct-messages') ? 'text-primary dark:text-primary-fixed border-b-2 border-primary dark:border-primary-fixed pb-1 font-medium' : 'text-on-surface-variant dark:text-surface-variant'
                    ]" href="/direct-messages">DM</Link>
                    <!-- <a class="text-on-surface-variant dark:text-surface-variant font-body-md text-body-md h-full flex items-center hover:text-primary transition-colors duration-150" href="#">Analytics</a> -->
                    <!-- <a class="text-on-surface-variant dark:text-surface-variant font-body-md text-body-md h-full flex items-center hover:text-primary transition-colors duration-150" href="#">Directory</a> -->
                </nav>

                <!-- Right: Actions & Profile -->
                <div class="flex items-center gap-4 relative">
                    <button class="relative p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-colors scale-95 active:scale-90 flex items-center justify-center cursor-pointer border-none bg-transparent">
                        <span class="material-symbols-outlined">notifications</span>
                        <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-error rounded-full border-2 border-surface"></span>
                    </button>
                    <Link href="/settings" class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-colors scale-95 active:scale-90 flex items-center justify-center">
                        <span class="material-symbols-outlined">settings</span>
                    </Link>

                    <!-- Interactive User Dropdown -->
                    <div class="relative">
                        <button @click="showUserMenu = !showUserMenu" class="w-9 h-9 rounded-full overflow-hidden border border-outline-variant cursor-pointer scale-95 active:scale-90 transition-transform flex items-center justify-center bg-primary-container text-on-primary font-bold text-sm border-none">
                            <img v-if="user.avatar" :src="user.avatar" class="w-full h-full object-cover" :alt="user.name" />
                            <span v-else>{{ getInitials(user.name) }}</span>
                        </button>
                        
                        <div v-if="showUserMenu" class="absolute right-0 mt-2 w-56 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-150">
                            <div class="px-4 py-2 border-b border-outline-variant">
                                <p class="font-bold text-on-surface truncate">{{ user.name }}</p>
                                <p class="text-xs text-on-surface-variant truncate">{{ user.email }}</p>
                            </div>
                            <Link href="/profile" class="flex items-center gap-2 px-4 py-2 text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md">
                                <span class="material-symbols-outlined text-[18px]">person</span>
                                My Profile
                            </Link>
                            <Link href="/settings" class="flex items-center gap-2 px-4 py-2 text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md">
                                <span class="material-symbols-outlined text-[18px]">settings</span>
                                Settings
                            </Link>
                            <div class="border-t border-outline-variant my-1"></div>
                            <Link href="/logout" method="post" as="button" @click="showUserMenu = false" class="w-full flex items-center gap-2 px-4 py-2 text-error hover:bg-error-container/20 transition-colors font-label-md text-label-md text-left cursor-pointer border-none bg-transparent">
                                <span class="material-symbols-outlined text-[18px]">logout</span>
                                Log out
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content Slot -->
            <main class="flex-1 w-full max-w-container-max mx-auto px-margin-desktop py-stack-lg flex flex-col">
                <!-- Breadcrumbs display if provided -->
                <nav v-if="breadcrumbs && breadcrumbs.length > 0" class="flex items-center gap-2 text-xs text-on-surface-variant mb-6 select-none">
                    <template v-for="(item, idx) in breadcrumbs" :key="idx">
                        <Link v-if="item.href" :href="item.href" class="hover:text-primary transition-colors font-medium">{{ item.title }}</Link>
                        <span v-else class="text-on-surface font-semibold">{{ item.title }}</span>
                        <span v-if="idx < breadcrumbs.length - 1" class="text-outline-variant mx-1">/</span>
                    </template>
                </nav>
                
                <slot />
            </main>

            <!-- Floating DM Widget -->
            <div v-if="currentUrl !== '/direct-messages'" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
                <!-- Expanded DM Box -->
                <div v-if="showDmWidget" class="w-80 sm:w-96 h-[480px] bg-surface-container-lowest border border-outline-variant rounded-xl shadow-xl flex flex-col mb-4 transition-all duration-300 overflow-hidden text-on-surface">
                    <!-- Header -->
                    <div class="bg-primary text-on-primary p-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">forum</span>
                            <span class="font-label-lg font-bold">Direct Messages</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button v-if="activeDmChat" @click="closeActiveChat" class="text-on-primary hover:bg-white/10 rounded p-1 cursor-pointer border-none bg-transparent flex items-center justify-center">
                                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            </button>
                            <button @click="toggleDmWidget" class="text-on-primary hover:bg-white/10 rounded p-1 cursor-pointer border-none bg-transparent flex items-center justify-center">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                            </button>
                        </div>
                    </div>

                    <!-- Contact List View -->
                    <div v-if="!activeDmChat" class="flex-1 overflow-y-auto p-2 divide-y divide-outline-variant">
                        <div v-for="contact in dmContacts" :key="contact.id" @click="openChat(contact)" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors cursor-pointer group">
                            <div class="relative shrink-0">
                                <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-sm shrink-0 border border-outline-variant">
                                    {{ getInitials(contact.name) }}
                                </div>
                                <div :class="['absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-surface-container-lowest', contact.status === 'online' ? 'bg-green-500' : 'bg-outline-variant']"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline">
                                    <h4 class="font-label-md font-semibold text-on-surface truncate group-hover:text-primary transition-colors">{{ contact.name }}</h4>
                                    <span v-if="contact.last_message_time" class="text-[10px] text-on-surface-variant select-none">{{ contact.last_message_time }}</span>
                                </div>
                                <p class="text-xs text-on-surface-variant truncate">{{ contact.last_message || contact.role }}</p>
                            </div>
                            <div v-if="contact.unread_count > 0" class="bg-error text-on-error font-label-sm text-label-sm px-1.5 py-0.5 rounded-full shrink-0">
                                {{ contact.unread_count }}
                            </div>
                        </div>
                        <div v-if="dmContacts.length === 0" class="text-center py-12 text-on-surface-variant text-xs select-none">
                            <span class="material-symbols-outlined text-[36px] text-outline mb-2">contacts</span>
                            <p>No contacts found.</p>
                        </div>
                    </div>

                    <!-- Active Chat History View -->
                    <div v-else class="flex-1 flex flex-col overflow-hidden bg-surface-container-low">
                        <!-- Contact Info Bar -->
                        <div class="bg-surface-container-lowest px-4 py-2 border-b border-outline-variant flex items-center gap-2">
                            <div class="relative shrink-0">
                                <div class="w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-xs shrink-0 border border-outline-variant">
                                    {{ getInitials(activeDmChat.name) }}
                                </div>
                                <div :class="['absolute bottom-0 right-0 w-2 h-2 rounded-full border-2 border-surface-container-lowest', activeDmChat.status === 'online' ? 'bg-green-500' : 'bg-outline-variant']"></div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-label-sm font-semibold text-on-surface truncate">{{ activeDmChat.name }}</h4>
                                <p class="text-[10px] text-on-surface-variant truncate">{{ activeDmChat.role }}</p>
                            </div>
                        </div>

                        <!-- Messages Container -->
                        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 flex flex-col">
                            <div v-for="msg in dmMessages" :key="msg.id" :class="['max-w-[75%] rounded-xl px-3.5 py-2 text-sm shadow-sm flex flex-col', msg.is_mine ? 'bg-primary text-on-primary self-end rounded-br-none' : 'bg-surface-container-lowest text-on-surface self-start rounded-bl-none']">
                                <p class="whitespace-pre-wrap leading-relaxed">{{ msg.message }}</p>
                                <span :class="['text-[9px] self-end mt-1', msg.is_mine ? 'text-on-primary/60' : 'text-on-surface-variant']">{{ msg.time }}</span>
                            </div>
                            <div v-if="dmMessages.length === 0" class="text-center text-on-surface-variant text-[11px] py-12 select-none">
                                Start of conversation. Send a message to say hello!
                            </div>
                        </div>

                        <!-- Input Bar -->
                        <div class="bg-surface-container-lowest p-3 border-t border-outline-variant flex gap-2">
                            <input v-model="newDmMessage" @keyup.enter="sendDmMessage" type="text" class="flex-1 bg-surface-container-low border border-outline-variant focus:border-primary rounded-lg px-3 py-1.5 text-xs text-on-surface focus:outline-none placeholder-outline" placeholder="Type a message..." />
                            <button @click="sendDmMessage" class="bg-primary hover:bg-on-primary-fixed-variant text-on-primary font-label-sm px-3.5 py-1.5 rounded-lg border-none cursor-pointer flex items-center justify-center transition-colors">
                                <span class="material-symbols-outlined text-[16px]">send</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Floating DM Button -->
                <button @click="toggleDmWidget" class="w-14 h-14 bg-primary text-on-primary hover:bg-on-primary-fixed-variant shadow-lg rounded-full flex items-center justify-center cursor-pointer border-none transition-all duration-300 transform hover:scale-105 active:scale-95">
                    <span class="material-symbols-outlined text-[24px]">{{ showDmWidget ? 'close' : 'chat_bubble' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
