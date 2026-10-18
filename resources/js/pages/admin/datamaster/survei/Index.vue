<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const formatTanggal = (dateStr: string | null) => {
    if (!dateStr) return null;
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

type MasterSurveiItem = {
    id: number;
    judul: string;
    deskripsi: string | null;
    batas_pengisian: 'sekali' | 'berkali_kali';
    tanggal_mulai: string | null;
    tanggal_selesai: string | null;
    target_roles: string[];
    is_active: boolean;
    pertanyaans_count: number;
    respons_count: number;
    user: { name: string };
};

type PaginationLink = { url: string | null; label: string; active: boolean };

const props = defineProps<{
    surveis: {
        data: MasterSurveiItem[];
        links: PaginationLink[];
        from: number;
        to: number;
        total: number;
    };
    filters: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/dashboard' },
    { title: 'Master Survei', href: '/admin/master-survei' },
];

const page = usePage();
const search = ref(props.filters.search || '');

watch(search, debounce((newSearch: string) => {
    router.get('/admin/master-survei', { search: newSearch || undefined }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300));

const destroySurvei = (id: number) => {
    if (!confirm('Apakah Anda yakin ingin menghapus Survei ini? Seluruh pertanyaan dan respon user akan terhapus.')) return;
    router.delete(`/admin/master-survei/${id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Manajemen Survei" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 bg-surface text-on-surface">

            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between select-none">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-on-surface">Manajemen Survei</h1>
                    <p class="text-sm text-on-surface-variant">Buat dan kelola instrumen survei dinamis untuk seluruh pegawai dan UPT.</p>
                </div>

                <Link href="/admin/master-survei/create" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-5 py-2.5 text-xs font-bold text-on-primary shadow-sm hover:bg-on-primary-fixed-variant transition-colors border-none no-underline">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    Buat Survei Baru
                </Link>
            </div>

            <!-- Flash Success -->
            <div v-if="page.props.flash?.success" class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-2 select-none">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                {{ page.props.flash.success }}
            </div>

            <!-- Search -->
            <div class="relative w-full max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                <input v-model="search" type="text" placeholder="Cari judul survei..." class="w-full h-10 pl-9 pr-3 rounded-xl border border-outline bg-surface-container-low text-xs text-on-surface focus:outline-none focus:border-primary" />
            </div>

            <!-- Table -->
            <div class="rounded-2xl border border-outline-variant bg-surface-container-lowest text-on-surface shadow-sm overflow-hidden">
                <div class="relative w-full overflow-x-auto">
                    <table class="w-full caption-bottom text-xs border-collapse">
                        <thead class="bg-surface-container border-b border-outline-variant text-on-surface-variant font-bold">
                            <tr>
                                <th class="h-11 px-4 text-left">Judul Survei</th>
                                <th class="h-11 px-4 text-center">Batas Pengisian</th>
                                <th class="h-11 px-4 text-center">Rentang Tanggal</th>
                                <th class="h-11 px-4 text-center">Soal</th>
                                <th class="h-11 px-4 text-center">Respon Masuk</th>
                                <th class="h-11 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr v-for="s in surveis.data" :key="s.id" class="hover:bg-surface-container-low transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-on-surface text-sm">{{ s.judul }}</div>
                                    <div class="text-[11px] text-on-surface-variant mt-0.5 line-clamp-1">{{ s.deskripsi || 'Tidak ada deskripsi' }}</div>
                                    <div class="text-[10px] text-outline mt-1 font-mono">Dibuat oleh: {{ s.user.name }}</div>
                                </td>

                                <td class="p-4 text-center">
                                    <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold select-none', s.batas_pengisian === 'sekali' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300']">
                                        {{ s.batas_pengisian === 'sekali' ? 'Hanya 1x' : 'Bebas / Berulang' }}
                                    </span>
                                </td>

                                <td class="p-4 text-center">
                                <div v-if="s.tanggal_mulai || s.tanggal_selesai" class="text-[11px] font-medium text-on-surface">
                                    {{ formatTanggal(s.tanggal_mulai) || 'Sekarang' }}
                                    <span class="text-outline mx-1">-</span>
                                    {{ formatTanggal(s.tanggal_selesai) || 'Seterusnya' }}
                                </div>
                                <span v-else class="text-outline text-[11px]">Tanpa Batas Waktu</span>
                            </td>

                                <td class="p-4 text-center font-bold font-mono">{{ s.pertanyaans_count }}</td>

                                <td class="p-4 text-center">
                                    <span class="font-bold text-primary px-2.5 py-1 rounded-md bg-primary/10">
                                        {{ s.respons_count }} Respon
                                    </span>
                                </td>

                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="`/admin/master-survei/${s.id}`" class="p-1.5 text-primary hover:bg-primary/10 rounded-lg transition-colors cursor-pointer no-underline inline-flex items-center" title="Lihat Hasil Survei">
                                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                                        </Link>
                                        <button type="button" @click="destroySurvei(s.id)" class="p-1.5 text-error hover:bg-error/10 rounded-lg transition-colors cursor-pointer border-none bg-transparent" title="Hapus Survei">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="surveis.data.length === 0">
                                <td colspan="6" class="p-8 text-center text-on-surface-variant select-none">
                                    Belum ada survei yang dibuat. Klik tombol "Buat Survei Baru" untuk memulai.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="surveis.links.length > 3" class="flex justify-between items-center text-xs text-on-surface-variant">
                <span>Menampilkan {{ surveis.from ?? 0 }} sampai {{ surveis.to ?? 0 }} dari {{ surveis.total }} survei</span>
                <div class="flex gap-1">
                    <template v-for="(link, key) in surveis.links" :key="key">
                        <div v-if="link.url === null" class="px-3 py-1.5 text-outline border border-transparent" v-html="link.label" />
                        <Link v-else :href="link.url" class="px-3 py-1.5 rounded-lg border transition-colors cursor-pointer" :class="link.active ? 'bg-primary text-on-primary border-primary font-semibold' : 'border-outline bg-surface-container text-on-surface'" v-html="link.label" />
                    </template>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
