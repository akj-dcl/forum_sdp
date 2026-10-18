<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Survei Layanan', href: '/survei-layanan' },
];

const daftarLayanan = [
    { name: 'Registrasi', min: 3, icon: 'how_to_reg', desc: '' },
    { name: 'Integrasi', min: 3, icon: 'sync_alt', desc: '' },
    { name: 'Kunjungan', min: 3, icon: 'groups', desc: '' },
    { name: 'Portir', min: 1, icon: 'door_front', desc: '' },
    { name: 'Perawatan Kesehatan', min: 2, icon: 'medical_services', desc: '' },
    { name: 'Keamanan', min: 2, icon: 'security', desc: '' },
    { name: 'SPPN', min: 5, icon: 'rule', desc: '' },
    { name: 'Self Service WBP', min: 2, icon: 'touch_app', desc: '' },
];

const opsiSarana = [
    { label: 'Manual', desc: 'Pencatatan manual tanpa perangkat khusus' },
    { label: 'Pembagian Pusat 2018', desc: 'Unit bantuan pusat pengadaan tahun 2018' },
    { label: 'Pembagian Pusat 2019', desc: 'Unit bantuan pusat pengadaan tahun 2019' },
    { label: 'Pengadaan Sendiri', desc: 'Perangkat mandiri yang diadakan oleh UPT' },
];

interface SurveyItem {
    nama_layanan: string;
    status_berjalan: boolean | null;
    sarana: string;
    status_kelengkapan: string;
    periode_pengadaan: string;
    jumlah_perangkat: number | null;
    foto_perangkat: File[];
    previewFotos: string[];
}

const step = ref<number>(1);
const activeIndex = ref<number>(0);
const selectedNames = ref<string[]>([]);
const multiFotoInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    surveys: [] as SurveyItem[],
});

const toggleLayanan = (nama: string) => {
    const idx = selectedNames.value.indexOf(nama);
    if (idx > -1) {
        selectedNames.value.splice(idx, 1);
    } else {
        selectedNames.value.push(nama);
    }
};

const pilihSemua = () => {
    if (selectedNames.value.length === daftarLayanan.length) {
        selectedNames.value = [];
    } else {
        selectedNames.value = daftarLayanan.map(l => l.name);
    }
};

const mulaiPengisian = () => {
    form.surveys = selectedNames.value.map(nama => ({
        nama_layanan: nama,
        status_berjalan: null,
        sarana: '',
        status_kelengkapan: '',
        periode_pengadaan: '',
        jumlah_perangkat: null,
        foto_perangkat: [],
        previewFotos: [],
    }));
    activeIndex.value = 0;
    step.value = 2;
};

const currentItem = computed(() => {
    return form.surveys[activeIndex.value] || null;
});

const currentRule = computed(() => {
    if (!currentItem.value) return null;
    return daftarLayanan.find(l => l.name === currentItem.value.nama_layanan);
});

const isCurrentIdeal = computed(() => {
    if (!currentRule.value || currentItem.value?.jumlah_perangkat === null) return null;
    return (currentItem.value?.jumlah_perangkat ?? 0) >= currentRule.value.min;
});

const triggerFotoInput = () => {
    multiFotoInput.value?.click();
};

const handleFotoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0 && currentItem.value) {
        for (let i = 0; i < target.files.length; i++) {
            const file = target.files[i];
            currentItem.value.foto_perangkat.push(file);
            currentItem.value.previewFotos.push(URL.createObjectURL(file));
        }
    }
    target.value = '';
};

const hapusSatuFoto = (fotoIndex: number) => {
    if (currentItem.value) {
        currentItem.value.foto_perangkat.splice(fotoIndex, 1);
        currentItem.value.previewFotos.splice(fotoIndex, 1);
    }
};

const nextItem = () => {
    if (activeIndex.value < form.surveys.length - 1) {
        activeIndex.value++;
    }
};

const prevItem = () => {
    if (activeIndex.value > 0) {
        activeIndex.value--;
    }
};

const isAllCompleted = computed(() => {
    if (form.surveys.length === 0) return false;
    return form.surveys.every(s => {
        if (s.status_berjalan === false) return true;
        if (s.status_berjalan === true) {
            const saranaFilled = !!s.sarana;
            const kelengkapanFilled = (s.sarana === 'Manual') || !!s.status_kelengkapan;
            const periodeFilled = (s.sarana !== 'Pengadaan Sendiri') || !!s.periode_pengadaan;
            const jumlahFilled = s.jumlah_perangkat !== null;
            return saranaFilled && kelengkapanFilled && periodeFilled && jumlahFilled;
        }
        return false;
    });
});

const submitSemua = () => {
    form.post('/survei-layanan', {
        preserveScroll: true,
        onSuccess: () => {
            step.value = 1;
            selectedNames.value = [];
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Survei Layanan Lapas/Rutan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-3xl mx-auto w-full py-6 flex flex-col gap-6">

            <!-- Flash Message -->
            <div
                v-if="page.props.flash?.success"
                class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-2 select-none"
            >
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                {{ page.props.flash.success }}
            </div>

            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-sm overflow-hidden flex flex-col">

                <!-- Header -->
                <div class="p-6 sm:p-8 border-b border-outline-variant bg-surface-container-low/40 flex flex-col gap-3 select-none">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-primary uppercase tracking-wider">
                            {{ step === 1 ? 'Langkah 1: Pilih Layanan' : `Langkah 2: Isi Data (${activeIndex + 1} dari ${form.surveys.length})` }}
                        </span>
                        <span v-if="step === 1 && selectedNames.length > 0" class="text-xs font-bold px-2.5 py-1 rounded-full bg-primary text-on-primary">
                            {{ selectedNames.length }} Dipilih
                        </span>
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold text-on-surface tracking-tight">Survei  Sarana Layanan</h1>
                        <p class="text-xs text-on-surface-variant mt-1"></p>
                    </div>
                </div>

                <!-- STEP 1: PILIH LAYANAN -->
                <div v-if="step === 1" class="p-6 sm:p-8 flex flex-col gap-5">
                    <div class="flex items-center justify-between select-none">
                        <div>
                            <h2 class="font-bold text-base text-on-surface">Pilih Satu atau Lebih Layanan</h2>
                            <p class="text-xs text-on-surface-variant mt-0.5">Centang layanan yang ingin disurvei sekaligus.</p>
                        </div>
                        <button
                            type="button"
                            @click="pilihSemua"
                            class="text-xs font-bold text-primary hover:underline border-none bg-transparent cursor-pointer"
                        >
                            {{ selectedNames.length === daftarLayanan.length ? 'Batal Pilih Semua' : 'Pilih Semua (8)' }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button
                            v-for="layanan in daftarLayanan"
                            :key="layanan.name"
                            type="button"
                            @click="toggleLayanan(layanan.name)"
                            :class="[
                                'p-4 rounded-xl border text-left cursor-pointer transition-all flex items-start gap-3.5 relative group',
                                selectedNames.includes(layanan.name)
                                    ? 'border-primary bg-primary/10 shadow-xs ring-1 ring-primary'
                                    : 'border-outline-variant bg-surface-container-low hover:border-primary/50 hover:bg-surface-container-high'
                            ]"
                        >
                            <div
                                :class="[
                                    'w-5 h-5 rounded-md border flex items-center justify-center shrink-0 mt-0.5 transition-colors',
                                    selectedNames.includes(layanan.name)
                                        ? 'bg-primary border-primary text-on-primary'
                                        : 'border-outline-variant bg-surface-container-lowest'
                                ]"
                            >
                                <span v-if="selectedNames.includes(layanan.name)" class="material-symbols-outlined text-[16px]">check</span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 :class="['text-sm font-bold truncate', selectedNames.includes(layanan.name) ? 'text-primary' : 'text-on-surface']">
                                        {{ layanan.name }}
                                    </h3>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-surface-container text-on-surface-variant shrink-0 ml-1 select-none">
                                        Min. {{ layanan.min }}
                                    </span>
                                </div>
                                <p class="text-xs text-on-surface-variant mt-1 line-clamp-2 leading-relaxed font-normal">
                                    {{ layanan.desc }}
                                </p>
                            </div>
                        </button>
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t border-outline-variant mt-2">
                        <span class="text-xs text-on-surface-variant">
                            {{ selectedNames.length }} layanan dipilih
                        </span>
                        <button
                            type="button"
                            :disabled="selectedNames.length === 0"
                            @click="mulaiPengisian"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-on-primary rounded-xl font-semibold text-sm hover:bg-on-primary-fixed-variant transition-colors disabled:opacity-50 cursor-pointer border-none shadow-sm"
                        >
                            <span>Lanjutkan Pengisian</span>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: FORM PENGISIAN DETAIL -->
                <div v-else-if="step === 2 && currentItem" class="p-6 sm:p-8 flex flex-col gap-6">

                    <!-- Tab Pills Layanan -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-outline-variant select-none">
                        <button
                            v-for="(srv, idx) in form.surveys"
                            :key="srv.nama_layanan"
                            type="button"
                            @click="activeIndex = idx"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-bold shrink-0 transition-all flex items-center gap-1.5 cursor-pointer border-none',
                                activeIndex === idx
                                    ? 'bg-primary text-on-primary shadow-xs'
                                    : 'bg-surface-container-high text-on-surface hover:bg-surface-variant'
                            ]"
                        >
                            <span>{{ srv.nama_layanan }}</span>
                            <span v-if="srv.status_berjalan === false" class="material-symbols-outlined text-[14px] text-error">pause_circle</span>
                            <span v-else-if="srv.status_berjalan === true && srv.sarana && srv.jumlah_perangkat !== null" class="material-symbols-outlined text-[14px] text-emerald-400">check_circle</span>
                            <span v-else class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        </button>
                    </div>

                    <!-- Header Layanan Aktif -->
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-primary uppercase tracking-wider">Layanan {{ activeIndex + 1 }} dari {{ form.surveys.length }}</span>
                            <h2 class="text-xl font-bold text-on-surface">{{ currentItem.nama_layanan }}</h2>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-surface-container text-on-surface-variant font-medium">
                            Standar Ideal: Minimal {{ currentRule?.min }} Unit
                        </span>
                    </div>

                    <!-- 1. Pertanyaan: Status Berjalan / Tidak -->
                    <div class="flex flex-col gap-2.5">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-bold text-on-surface">1. Apakah layanan ini sedang berjalan?</label>
                            <!-- NOTIFIKASI HANYA MUNCUL JIKA NULL -->
                            <span v-if="currentItem.status_berjalan === null" class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-500/20 select-none">
                                <span class="material-symbols-outlined text-[14px]">warning</span> Harap Diisi
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="currentItem.status_berjalan = true"
                                :class="[
                                    'p-3.5 rounded-xl border text-center font-bold text-xs cursor-pointer transition-all flex items-center justify-center gap-2',
                                    currentItem.status_berjalan === true
                                        ? 'border-primary bg-primary/10 text-primary ring-1 ring-primary'
                                        : 'border-outline bg-surface-container-low text-on-surface'
                                ]"
                            >
                                <span class="material-symbols-outlined text-sm">play_circle</span>
                                Berjalan
                            </button>
                            <button 
                                type="button"
                                @click="currentItem.status_berjalan = false; currentItem.sarana = ''; currentItem.status_kelengkapan = ''; currentItem.periode_pengadaan = ''; currentItem.jumlah_perangkat = 0;"
                                :class="[
                                    'p-3.5 rounded-xl border text-center font-bold text-xs cursor-pointer transition-all flex items-center justify-center gap-2',
                                    currentItem.status_berjalan === false
                                        ? 'border-error bg-error/10 text-error ring-1 ring-error'
                                        : 'border-outline bg-surface-container-low text-on-surface'
                                ]"
                            >
                                <span class="material-symbols-outlined text-sm">pause_circle</span>
                                Tidak Berjalan
                            </button>
                        </div>
                    </div>

                    <!-- BAGIAN DETAIL JIKA BERJALAN -->
                    <template v-if="currentItem.status_berjalan === true">
                        <!-- 2. Sarana yang Digunakan -->
                        <div class="flex flex-col gap-2.5">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-bold text-on-surface">2. Sarana yang Digunakan:</label>
                                <!-- NOTIFIKASI HANYA MUNCUL JIKA KOSONG -->
                                <span v-if="!currentItem.sarana" class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-500/20 select-none">
                                    <span class="material-symbols-outlined text-[14px]">warning</span> Harap Diisi
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <button
                                    v-for="s in opsiSarana"
                                    :key="s.label"
                                    type="button"
                                    @click="currentItem.sarana = s.label"
                                    :class="[
                                        'p-3 rounded-xl border text-left text-xs cursor-pointer transition-all',
                                        currentItem.sarana === s.label
                                            ? 'border-primary bg-primary/10 text-primary font-bold ring-1 ring-primary'
                                            : 'border-outline-variant bg-surface-container-low text-on-surface hover:bg-surface-container-high'
                                    ]"
                                >
                                    {{ s.label }}
                                </button>
                            </div>
                        </div>

                        <!-- 3. Kelengkapan (Jika Bukan Manual) -->
                        <div v-if="currentItem.sarana && currentItem.sarana !== 'Manual'" class="p-4 bg-surface-container-low rounded-xl border border-outline-variant flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-on-surface">Kelengkapan Sarana:</label>
                                <!-- NOTIFIKASI HANYA MUNCUL JIKA KOSONG -->
                                <span v-if="!currentItem.status_kelengkapan" class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20 select-none">
                                    <span class="material-symbols-outlined text-[13px]">warning</span> Harap Diisi
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <label
                                    :class="[
                                        'flex items-center gap-2 p-2.5 rounded-lg border text-xs font-semibold cursor-pointer',
                                        currentItem.status_kelengkapan === 'Lengkap'
                                            ? 'border-primary bg-white dark:bg-slate-900 text-primary'
                                            : 'border-outline bg-transparent text-on-surface'
                                    ]"
                                >
                                    <input type="radio" value="Lengkap" v-model="currentItem.status_kelengkapan" class="accent-primary" />
                                    Lengkap
                                </label>
                                <label
                                    :class="[
                                        'flex items-center gap-2 p-2.5 rounded-lg border text-xs font-semibold cursor-pointer',
                                        currentItem.status_kelengkapan === 'Tidak Lengkap'
                                            ? 'border-primary bg-white dark:bg-slate-900 text-primary'
                                            : 'border-outline bg-transparent text-on-surface'
                                    ]"
                                >
                                    <input type="radio" value="Tidak Lengkap" v-model="currentItem.status_kelengkapan" class="accent-primary" />
                                    Tidak Lengkap
                                </label>
                            </div>
                        </div>

                        <!-- 4. Periode Pengadaan (Jika Pengadaan Sendiri) -->
                        <div v-if="currentItem.sarana === 'Pengadaan Sendiri'" class="p-4 bg-surface-container-low rounded-xl border border-outline-variant flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-on-surface">Tahun Pengadaan Sendiri:</label>
                                <!-- NOTIFIKASI HANYA MUNCUL JIKA KOSONG -->
                                <span v-if="!currentItem.periode_pengadaan" class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20 select-none">
                                    <span class="material-symbols-outlined text-[13px]">warning</span> Harap Diisi
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <label
                                    :class="[
                                        'flex items-center gap-2 p-2.5 rounded-lg border text-xs font-semibold cursor-pointer',
                                        currentItem.periode_pengadaan === 'Sebelum 2024'
                                            ? 'border-primary bg-white dark:bg-slate-900 text-primary'
                                            : 'border-outline bg-transparent text-on-surface'
                                    ]"
                                >
                                    <input type="radio" value="Sebelum 2024" v-model="currentItem.periode_pengadaan" class="accent-primary" />
                                    Sebelum 2024
                                </label>
                                <label
                                    :class="[
                                        'flex items-center gap-2 p-2.5 rounded-lg border text-xs font-semibold cursor-pointer',
                                        currentItem.periode_pengadaan === 'Setelah 2024'
                                            ? 'border-primary bg-white dark:bg-slate-900 text-primary'
                                            : 'border-outline bg-transparent text-on-surface'
                                    ]"
                                >
                                    <input type="radio" value="Setelah 2024" v-model="currentItem.periode_pengadaan" class="accent-primary" />
                                    Setelah 2024
                                </label>
                            </div>
                        </div>

                        <!-- 5. Jumlah Perangkat -->
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-bold text-on-surface">3. Jumlah Perangkat Aktif:</label>
                                <div class="flex items-center gap-2">
                                    <!-- NOTIFIKASI HANYA MUNCUL JIKA NULL -->
                                    <span v-if="currentItem.jumlah_perangkat === null" class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-500/20 select-none">
                                        <span class="material-symbols-outlined text-[14px]">warning</span> Harap Diisi
                                    </span>
                                    <span class="text-xs text-on-surface-variant font-medium select-none">
                                        Minimal: {{ currentRule?.min }} Unit
                                    </span>
                                </div>
                            </div>

                            <input
                                v-model.number="currentItem.jumlah_perangkat"
                                type="number"
                                min="0"
                                placeholder="Contoh: 3"
                                class="w-full h-11 px-4 border border-outline rounded-xl bg-surface-container-low text-on-surface text-base focus:border-primary focus:outline-none"
                            />

                            <!-- Banner Status Hanya Muncul Jika Sudah Diisi Angka -->
                            <div v-if="currentItem.jumlah_perangkat !== null" :class="[
                                'p-2.5 rounded-xl border flex items-center gap-2 text-xs font-semibold select-none transition-all',
                                isCurrentIdeal
                                    ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400'
                                    : 'bg-amber-500/10 border-amber-500/30 text-amber-600 dark:text-amber-400'
                            ]">
                                <span class="material-symbols-outlined text-[18px]">{{ isCurrentIdeal ? 'check_circle' : 'warning' }}</span>
                                <span>Status: <strong>{{ isCurrentIdeal ? 'Ideal' : 'Tidak Ideal' }}</strong> (Minimal rekomendasi: {{ currentRule?.min }} unit)</span>
                            </div>
                        </div>

                        <!-- 6. Lampiran Foto -->
                        <div class="flex flex-col gap-2.5">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-bold text-on-surface">4. Lampiran Foto Perangkat (Bisa lebih dari 1):</label>
                                <span v-if="currentItem.previewFotos.length > 0" class="text-xs text-on-surface-variant font-semibold">
                                    {{ currentItem.previewFotos.length }} foto terpilih
                                </span>
                            </div>

                            <div v-if="currentItem.previewFotos.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div
                                    v-for="(src, fotoIdx) in currentItem.previewFotos"
                                    :key="fotoIdx"
                                    class="relative group rounded-xl overflow-hidden border border-outline-variant aspect-video bg-surface-container-low"
                                >
                                    <img :src="src" class="w-full h-full object-cover" />
                                    <button
                                        type="button"
                                        @click="hapusSatuFoto(fotoIdx)"
                                        class="absolute top-1.5 right-1.5 bg-error text-on-error w-6 h-6 rounded-md border-none cursor-pointer flex items-center justify-center shadow-md hover:opacity-90"
                                        title="Hapus foto ini"
                                    >
                                        <span class="material-symbols-outlined text-[14px]">close</span>
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    @click="triggerFotoInput"
                                    class="border-2 border-dashed border-outline-variant hover:border-primary rounded-xl flex flex-col items-center justify-center p-3 text-on-surface-variant hover:text-primary cursor-pointer transition-colors bg-surface-container-low/30 aspect-video"
                                >
                                    <span class="material-symbols-outlined text-[24px]">add_photo_alternate</span>
                                    <span class="text-[11px] font-bold mt-1">+ Tambah Foto</span>
                                </button>
                            </div>

                            <div
                                v-else
                                class="border-2 border-dashed border-outline-variant hover:border-primary/50 rounded-xl p-5 flex flex-col items-center justify-center text-center cursor-pointer bg-surface-container-low/50"
                                @click="triggerFotoInput"
                            >
                                <span class="material-symbols-outlined text-[32px] text-primary mb-1">add_a_photo</span>
                                <p class="text-xs font-bold text-on-surface">Pilih satu atau beberapa foto untuk {{ currentItem.nama_layanan }}</p>
                                <p class="text-[10px] text-on-surface-variant mt-0.5">Format JPG, JPEG, atau PNG</p>
                            </div>

                            <input
                                ref="multiFotoInput"
                                type="file"
                                accept="image/*"
                                multiple
                                class="hidden"
                                @change="handleFotoChange"
                            />
                        </div>
                    </template>

                    <!-- Navigasi Bawah -->
                    <div class="flex justify-between items-center pt-5 border-t border-outline-variant mt-3">
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="step = 1"
                                class="px-3.5 py-2 border border-outline text-on-surface rounded-xl text-xs font-semibold hover:bg-surface-container-high cursor-pointer bg-transparent"
                            >
                                Ubah Pilihan Layanan
                            </button>
                            <button
                                v-if="activeIndex > 0"
                                type="button"
                                @click="prevItem"
                                class="px-3.5 py-2 border border-outline text-on-surface rounded-xl text-xs font-semibold hover:bg-surface-container-high cursor-pointer bg-transparent"
                            >
                                Sebelumnya
                            </button>
                        </div>

                        <div class="flex gap-2">
                            <button
                                v-if="activeIndex < form.surveys.length - 1"
                                type="button"
                                @click="nextItem"
                                class="px-5 py-2.5 bg-surface-container-high text-on-surface rounded-xl font-bold text-xs hover:bg-surface-variant cursor-pointer border-none flex items-center gap-1"
                            >
                                <span>Berikutnya</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </button>

                            <button
                                type="button"
                                :disabled="form.processing || !isAllCompleted"
                                @click="submitSemua"
                                class="px-6 py-2.5 bg-primary text-on-primary rounded-xl font-bold text-sm hover:bg-on-primary-fixed-variant transition-colors disabled:opacity-50 cursor-pointer border-none shadow-sm flex items-center gap-1.5"
                            >
                                <span class="material-symbols-outlined text-sm">{{ form.processing ? 'hourglass_top' : 'send' }}</span>
                                <span>{{ form.processing ? 'Menyimpan...' : `Kirim Semua (${form.surveys.length})` }}</span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>
