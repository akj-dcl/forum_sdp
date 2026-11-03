<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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
type Question = {
    id: number;
    teks_pertanyaan: string;
    tipe_jawaban: 'teks' | 'pilihan' | 'angka' | 'foto';
    wajib_diisi: boolean;
    urutan: number;
};

type ResponseItem = {
    id: number;
    user: { name: string; nip: string | null };
    upt: { name: string } | null;
    jawaban: Record<string, any>;
    total_skor?: number;
    created_at: string;
    updated_at: string;
};

const props = defineProps<{
    survei: {
        id: number;
        judul: string;
        deskripsi: string | null;
        batas_pengisian: string;
        tanggal_mulai: string | null;
        tanggal_selesai: string | null;
        pertanyaans: Question[];
        respons: ResponseItem[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/dashboard' },
    { title: 'Master Survei', href: '/admin/master-survei' },
    { title: `Hasil: ${props.survei.judul}`, href: `/admin/master-survei/${props.survei.id}` },
];

const searchQuery = ref('');

const filteredResponses = computed(() => {
    if (!searchQuery.value.trim()) return props.survei.respons;
    const q = searchQuery.value.toLowerCase();
    return props.survei.respons.filter(r =>
        (r.user?.name && r.user.name.toLowerCase().includes(q)) ||
        (r.user?.nip && r.user.nip.toLowerCase().includes(q)) ||
        (r.upt?.name && r.upt.name.toLowerCase().includes(q))
    );
});

const selectedRespon = ref<ResponseItem | null>(null);

const bukaDetail = (r: ResponseItem) => {
    selectedRespon.value = r;
};

const tutupDetail = () => {
    selectedRespon.value = null;
};
</script>

<template>
    <Head :title="`Hasil Survei: ${survei.judul}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 bg-surface text-on-surface">

            <!-- Page Header -->
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between select-none">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <Link href="/admin/master-survei" class="text-xs font-bold text-primary hover:underline flex items-center gap-1 no-underline">
                            <span class="material-symbols-outlined text-sm">arrow_back</span>
                            Kembali ke Master Survei
                        </Link>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-on-surface">{{ survei.judul }}</h1>
                    <p class="text-xs text-on-surface-variant mt-0.5">{{ survei.deskripsi || 'Rekapitulasi seluruh jawaban responden yang telah masuk.' }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="px-4 py-2 rounded-xl bg-primary/10 text-primary font-bold text-xs border border-primary/20">
                        Total Jawaban: {{ survei.respons.length }}
                    </span>
                </div>
            </div>

            <!-- Survey Info Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
                    <span class="text-[11px] text-on-surface-variant font-medium">Batas Pengisian</span>
                    <p class="text-sm font-bold text-on-surface mt-1">
                        {{ survei.batas_pengisian === 'sekali' ? 'Hanya 1 Kali' : 'Bebas / Berulang' }}
                    </p>
                </div>
                <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
                    <span class="text-[11px] text-on-surface-variant font-medium">Periode Aktif</span>
                    <p class="text-sm font-bold text-on-surface mt-1">
                        {{ formatTanggal(survei.tanggal_mulai) || 'Sekarang' }}
                        <span class="text-outline font-normal mx-1">-</span>
                        {{ formatTanggal(survei.tanggal_selesai) || 'Seterusnya' }}
                    </p>
                </div>
                <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
                    <span class="text-[11px] text-on-surface-variant font-medium">Jumlah Pertanyaan</span>
                    <p class="text-sm font-bold text-on-surface mt-1">{{ survei.pertanyaans.length }} Soal</p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari nama pegawai, NIP, atau UPT..."
                    class="w-full h-10 pl-9 pr-3 rounded-xl border border-outline bg-surface-container-low text-xs text-on-surface focus:outline-none focus:border-primary"
                />
            </div>

            <!-- Respondents Table -->
            <div class="rounded-2xl border border-outline-variant bg-surface-container-lowest text-on-surface shadow-sm overflow-hidden">
                <div class="relative w-full overflow-x-auto">
                    <table class="w-full caption-bottom text-xs border-collapse">
                        <thead class="bg-surface-container border-b border-outline-variant text-on-surface-variant font-bold">
                            <tr>
                                <th class="h-11 px-4 text-left">Pegawai / Responden</th>
                                <th class="h-11 px-4 text-left">UPT</th>
                                <th class="h-11 px-4 text-center">Waktu Kirim</th>
                                <th class="h-11 px-4 text-center">Waktu Update</th>
                                <th class="h-11 px-4 text-center">Total Skor</th>
                                <th class="h-11 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr v-for="r in filteredResponses" :key="r.id" class="hover:bg-surface-container-low transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-on-surface">{{ r.user?.name || 'User Terhapus' }}</div>
                                    <div class="text-[11px] text-on-surface-variant mt-0.5 font-mono">NIP: {{ r.user?.nip || '-' }}</div>
                                </td>

                                <td class="p-4 font-medium text-on-surface">
                                    {{ r.upt?.name || 'Kantor Wilayah / Pusat' }}
                                </td>

                                <td class="p-4 text-center text-on-surface-variant font-mono text-[11px]">
                                    {{ new Date(r.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) }}
                                </td>

                                <td class="p-4 text-center text-on-surface-variant font-mono text-[11px]">
                                    {{ r.updated_at !== r.created_at ? new Date(r.updated_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-' }}
                                </td>

                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary/10 text-primary font-black font-mono text-xs">
                                        {{ r.total_skor ?? 0 }}
                                    </span>
                                </td>

                                <td class="p-4 text-right">
                                    <button
                                        type="button"
                                        @click="bukaDetail(r)"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 hover:bg-primary/20 text-primary font-bold text-xs cursor-pointer border-none transition-colors"
                                    >
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        Lihat Jawaban
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="filteredResponses.length === 0">
                                <td colspan="5" class="p-8 text-center text-on-surface-variant select-none">
                                    <span class="material-symbols-outlined text-[40px] text-outline mb-1">inbox</span>
                                    <p class="text-xs">Belum ada respon yang masuk atau ditemukan.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- MODAL DETAIL JAWABAN RESPONDEN -->
        <div v-if="selectedRespon" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 select-none">
            <div class="w-full max-w-2xl bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[85vh]">

                <!-- Modal Header -->
                <div class="bg-primary text-on-primary px-6 py-4 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="font-bold text-sm">Jawaban: {{ selectedRespon.user?.name }}</h3>
                        <p class="text-xs text-on-primary/80 mt-0.5">UPT: {{ selectedRespon.upt?.name || '-' }}</p>
                    </div>
                    <button @click="tutupDetail" class="text-on-primary hover:bg-white/10 rounded-lg p-1 cursor-pointer border-none bg-transparent flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <!-- Modal Body: Loop melalui semua pertanyaan survei -->
                <div class="p-6 overflow-y-auto flex flex-col gap-4">
                    <div
                        v-for="(q, idx) in survei.pertanyaans"
                        :key="q.id"
                        class="p-4 rounded-xl bg-surface-container-low border border-outline-variant flex flex-col gap-2"
                    >
                        <div class="flex items-baseline justify-between gap-2">
                            <span class="text-xs font-bold text-on-surface">
                                {{ idx + 1 }}. {{ q.teks_pertanyaan }}
                            </span>
                            <span class="text-[10px] text-outline uppercase font-mono bg-surface-container-highest px-1.5 py-0.5 rounded">{{ q.tipe_jawaban }}</span>
                        </div>

                        <!-- Jika Soal Dilewati (Skip Logic) atau Tidak Dijawab -->
                        <div v-if="selectedRespon.jawaban[q.id] === null || selectedRespon.jawaban[q.id] === undefined" class="text-xs text-on-surface-variant italic p-2.5">
                            (Soal dilewati / Tidak dijawab)
                        </div>

                        <!-- Jawaban Tipe Foto -->
                        <div v-else-if="q.tipe_jawaban === 'foto'" class="mt-1">
                            <div class="relative w-full max-w-xs rounded-lg overflow-hidden border border-outline-variant">
                                <img :src="selectedRespon.jawaban[q.id]" class="w-full h-40 object-cover" />
                                <a :href="selectedRespon.jawaban[q.id]" target="_blank" class="block text-center py-2 bg-black/70 hover:bg-black text-white text-[10px] font-bold no-underline transition-colors">
                                    Buka Foto Asli
                                </a>
                            </div>
                        </div>

                        <!-- Jawaban Tipe Teks / Angka / Pilihan -->
                        <div v-else class="text-xs text-on-surface bg-surface-container-lowest p-3 rounded-lg border border-outline-variant/50 font-medium whitespace-pre-wrap">
                            {{ selectedRespon.jawaban[q.id] }}
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-outline-variant flex justify-end shrink-0">
                    <button type="button" @click="tutupDetail" class="px-5 py-2.5 bg-surface-container-high hover:bg-surface-variant text-on-surface rounded-xl text-xs font-bold border-none cursor-pointer">
                        Close
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
