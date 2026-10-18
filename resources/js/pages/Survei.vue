<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import type { BreadcrumbItem } from '@/types';

type OptionItem = {
    teks: string;
    aksi?: 'lanjut' | 'selesai' | 'lompat';
    target_urutan?: number | null;
};

type Question = {
    id: number;
    teks_pertanyaan: string;
    tipe_jawaban: 'teks' | 'pilihan' | 'angka' | 'foto';
    opsi_pilihan: any[]; // Bisa string biasa atau OptionItem objek
    wajib_diisi: boolean;
    urutan: number;
};

type SurveyItem = {
    id: number;
    judul: string;
    deskripsi: string | null;
    batas_pengisian: 'sekali' | 'berkali_kali';
    tanggal_mulai: string | null;
    tanggal_selesai: string | null;
    total_pertanyaan: number;
    pertanyaans: Question[];
    sudah_diisi: boolean;
    existingResponse: {
        id: number;
        jawaban: Record<string, any>;
        tanggal_submit: string;
        tanggal_update: string;
    } | null;
    isReadOnly: boolean;
};

const props = defineProps<{
    surveis: SurveyItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Survei Pegawai', href: '/survei' },
];

const page = usePage();

// State Survei Aktif
const activeSurvei = ref<SurveyItem | null>(null);

// State Pertanyaan Satu-per-Satu & Riwayat Lompatan
const currentQuestionIndex = ref<number>(0);
const questionHistory = ref<number[]>([0]); // Menyimpan jejak indeks soal yang dilewati
const skippedQuestionIds = ref<number[]>([]); // Menyimpan ID soal yang sengaja di-skip

const form = useForm({
    jawaban: {} as Record<string, any>,
    jawaban_foto: {} as Record<string, File>,
    soal_dilewati: [] as number[],
});

const photoPreviews = ref<Record<string, string>>({});

// Buka Survei
const bukaSurvei = (s: SurveyItem) => {
    activeSurvei.value = s;
    currentQuestionIndex.value = 0;
    questionHistory.value = [0];
    skippedQuestionIds.value = [];
    photoPreviews.value = {};

    if (s.existingResponse?.jawaban) {
        form.jawaban = { ...s.existingResponse.jawaban };
    } else {
        form.jawaban = {};
    }
    form.jawaban_foto = {};
};

const tutupSurvei = () => {
    activeSurvei.value = null;
    form.reset();
    photoPreviews.value = {};
};

// Soal yang sedang aktif tampil di layar
const currentQuestion = computed(() => {
    if (!activeSurvei.value || activeSurvei.value.pertanyaans.length === 0) return null;
    return activeSurvei.value.pertanyaans[currentQuestionIndex.value] || null;
});

// Helper parsing opsi jawaban (mendukung string biasa atau objek percabangan)
const parseOption = (opt: any): OptionItem => {
    if (typeof opt === 'string') {
        return { teks: opt, aksi: 'lanjut', target_urutan: null };
    }
    return {
        teks: opt?.teks || '',
        aksi: opt?.aksi || 'lanjut',
        target_urutan: opt?.target_urutan || null,
    };
};

// Cek aksi dari jawaban pilihan ganda saat ini
const currentSelectedOptionAction = computed(() => {
    if (!currentQuestion.value || currentQuestion.value.tipe_jawaban !== 'pilihan') return 'lanjut';
    const answer = form.jawaban[currentQuestion.value.id];
    if (!answer) return 'lanjut';

    const found = currentQuestion.value.opsi_pilihan?.find((o: any) => {
        const parsed = parseOption(o);
        return parsed.teks === answer;
    });

    return found ? parseOption(found) : { teks: answer, aksi: 'lanjut', target_urutan: null };
});

// Apakah soal saat ini merupakan soal terakhir (atau user memilih opsi yang langsung selesai)
const isLastQuestion = computed(() => {
    if (!activeSurvei.value) return false;
    if (currentSelectedOptionAction.value.aksi === 'selesai') return true;
    return currentQuestionIndex.value === activeSurvei.value.pertanyaans.length - 1;
});

// Cek apakah soal saat ini kosong
const isCurrentQuestionEmpty = computed(() => {
    if (!currentQuestion.value) return false;
    const q = currentQuestion.value;
    const val = form.jawaban[q.id];
    if (q.tipe_jawaban === 'foto') {
        return !form.jawaban_foto[q.id] && !form.jawaban[q.id];
    }
    return val === null || val === undefined || val === '';
});

// Navigasi Maju (Menerapkan Skip Logic)
const handleNext = () => {
    if (!activeSurvei.value || !currentQuestion.value) return;

    const action = currentSelectedOptionAction.value;
    const totalQuestions = activeSurvei.value.pertanyaans.length;

    let nextIndex = currentQuestionIndex.value + 1;

    // Logika Lompat ke Soal Tertentu
    if (action.aksi === 'lompat' && action.target_urutan) {
        const targetIdx = action.target_urutan - 1; // Konversi ke index 0-based
        if (targetIdx >= 0 && targetIdx < totalQuestions) {
            // Tandai semua soal di antara nomor ini dan target sebagai 'dilewati'
            for (let i = currentQuestionIndex.value + 1; i < targetIdx; i++) {
                const skippedId = activeSurvei.value.pertanyaans[i].id;
                if (!skippedQuestionIds.value.includes(skippedId)) {
                    skippedQuestionIds.value.push(skippedId);
                }
            }
            nextIndex = targetIdx;
        }
    }

    if (nextIndex < totalQuestions) {
        currentQuestionIndex.value = nextIndex;
        questionHistory.value.push(nextIndex);
    }
};

// Navigasi Mundur (History Back)
const handleBack = () => {
    if (questionHistory.value.length > 1) {
        questionHistory.value.pop(); // Hapus posisi saat ini
        const prevIdx = questionHistory.value[questionHistory.value.length - 1];
        currentQuestionIndex.value = prevIdx;
    }
};

// Handle Foto Upload
const handlePhotoUpload = (qId: number, e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.jawaban_foto[qId] = file;
        photoPreviews.value[qId] = URL.createObjectURL(file);
    }
};

const hapusFotoBaru = (qId: number) => {
    delete form.jawaban_foto[qId];
    delete photoPreviews.value[qId];
};

// Kirim Jawaban
const submitJawaban = () => {
    if (!activeSurvei.value) return;

    form.soal_dilewati = skippedQuestionIds.value;

    if (activeSurvei.value.existingResponse) {
        form.post(`/survei/${activeSurvei.value.id}/update`, {
            preserveScroll: true,
            onSuccess: () => tutupSurvei(),
        });
    } else {
        form.post(`/survei/${activeSurvei.value.id}`, {
            preserveScroll: true,
            onSuccess: () => tutupSurvei(),
        });
    }
};
</script>

<template>
    <Head :title="activeSurvei ? activeSurvei.judul : 'Survei & Kuisioner Pegawai'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-3xl mx-auto w-full py-6 flex flex-col gap-6">

            <!-- Flash Messages -->
            <div v-if="page.props.flash?.success" class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-2 select-none">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                {{ page.props.flash.success }}
            </div>

            <div v-if="page.props.flash?.error" class="rounded-xl border border-error/20 bg-error/10 px-4 py-3 text-sm font-semibold text-error flex items-center gap-2 select-none">
                <span class="material-symbols-outlined text-[20px]">error</span>
                {{ page.props.flash.error }}
            </div>

            <!-- TAMPILAN 1: DAFTAR KARTU SURVEI -->
            <div v-if="!activeSurvei" class="flex flex-col gap-6">
                <div class="border-b border-outline-variant pb-4 select-none">
                    <h1 class="text-2xl font-bold text-on-surface">Survei & Kuisioner Aktif</h1>
                    <p class="text-xs text-on-surface-variant mt-1">Pilih survei untuk mengisi atau mengubah jawaban Anda.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="s in surveis" :key="s.id" class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant shadow-sm flex flex-col justify-between gap-4">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <span :class="['text-[10px] font-bold px-2.5 py-0.5 rounded-full select-none', s.sudah_diisi ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-primary/10 text-primary border border-primary/20']">
                                    {{ s.sudah_diisi ? '✓ Sudah Diisi' : 'Belum Diisi' }}
                                </span>
                                <span v-if="s.tanggal_selesai" class="text-[11px] text-outline font-medium">s/d {{ s.tanggal_selesai }}</span>
                            </div>

                            <h2 class="text-base font-bold text-on-surface">{{ s.judul }}</h2>
                            <p class="text-xs text-on-surface-variant line-clamp-2 leading-relaxed">{{ s.deskripsi || 'Tidak ada deskripsi.' }}</p>
                        </div>

                        <div class="pt-3 border-t border-outline-variant flex items-center justify-between">
                            <span class="text-xs text-on-surface-variant font-medium">{{ s.total_pertanyaan }} Soal</span>
                            <button type="button" @click="bukaSurvei(s)" :class="['inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold cursor-pointer border-none shadow-xs', s.sudah_diisi ? 'bg-surface-container-high hover:bg-surface-variant text-on-surface' : 'bg-primary hover:bg-on-primary-fixed-variant text-on-primary']">
                                <span class="material-symbols-outlined text-[16px]">{{ s.sudah_diisi ? 'edit_note' : 'play_arrow' }}</span>
                                <span>{{ s.sudah_diisi ? 'Lihat / Edit Jawaban' : 'Mulai Isi Survei' }}</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="surveis.length === 0" class="col-span-2 p-12 text-center text-on-surface-variant rounded-2xl border border-outline-variant bg-surface-container-lowest select-none">
                        <span class="material-symbols-outlined text-[48px] text-outline mb-2">task</span>
                        <p class="font-bold text-sm">Tidak ada survei aktif untuk Anda saat ini.</p>
                    </div>
                </div>
            </div>

            <!-- TAMPILAN 2: PENGISIAN SATU PER SATU (ONE-BY-ONE CARD) -->
            <div v-else-if="currentQuestion" class="flex flex-col gap-5">

                <!-- Header Atas: Tombol Keluar & Progress Bar -->
                <div class="flex items-center justify-between select-none">
                    <button type="button" @click="tutupSurvei" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl border border-outline text-xs font-bold text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer bg-transparent">
                        <span class="material-symbols-outlined text-sm">close</span>
                        <span>Keluar Survei</span>
                    </button>

                    <!-- Indikator Langkah -->
                    <span class="text-xs font-bold text-primary">
                        Soal {{ currentQuestionIndex + 1 }} dari {{ activeSurvei.pertanyaans.length }}
                    </span>
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden select-none">
                    <div class="bg-primary h-full transition-all duration-300 rounded-full" :style="{ width: `${((currentQuestionIndex + 1) / activeSurvei.pertanyaans.length) * 100}%` }"></div>
                </div>

                <!-- Banner Mode Edit -->
                <div v-if="activeSurvei.existingResponse && !activeSurvei.isReadOnly" class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-semibold flex items-center gap-2 select-none">
                    <span class="material-symbols-outlined text-[18px]">edit_note</span>
                    <span>Mode Edit Tanggapan (Terakhir disimpan: {{ activeSurvei.existingResponse.tanggal_update || activeSurvei.existingResponse.tanggal_submit }})</span>
                </div>

                <!-- KARTU SOAL TUNGGAL (ONE-BY-ONE) -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-3xl p-6 sm:p-10 shadow-sm flex flex-col gap-6">

                    <div class="flex flex-col gap-2">
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-xs font-bold text-primary uppercase tracking-wider select-none">Pertanyaan #{{ currentQuestionIndex + 1 }}</span>
                            <!-- NOTIFIKASI HARAP DIISI -->
                            <span v-if="currentQuestion.wajib_diisi && isCurrentQuestionEmpty" class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-500/20 select-none">
                                <span class="material-symbols-outlined text-[13px]">warning</span> Harap Diisi
                            </span>
                        </div>

                        <h2 class="text-xl sm:text-2xl font-bold text-on-surface leading-snug">
                            {{ currentQuestion.teks_pertanyaan }}
                            <span v-if="currentQuestion.wajib_diisi" class="text-error">*</span>
                        </h2>
                    </div>

                    <!-- AREA INPUT JAWABAN -->
                    <div class="pt-2">
                        <!-- 1. Tipe Pilihan Ganda -->
                        <div v-if="currentQuestion.tipe_jawaban === 'pilihan' && currentQuestion.opsi_pilihan" class="flex flex-col gap-3">
                            <button
                                v-for="(optRaw, oIdx) in currentQuestion.opsi_pilihan"
                                :key="oIdx"
                                type="button"
                                @click="form.jawaban[currentQuestion.id] = parseOption(optRaw).teks"
                                :class="[
                                    'p-4 rounded-2xl border text-left cursor-pointer transition-all flex items-center justify-between',
                                    form.jawaban[currentQuestion.id] === parseOption(optRaw).teks
                                        ? 'border-primary bg-primary/10 text-primary font-bold ring-1 ring-primary'
                                        : 'border-outline-variant bg-surface-container-low text-on-surface hover:bg-surface-container-high'
                                ]"
                            >
                                <span class="text-sm font-semibold">{{ parseOption(optRaw).teks }}</span>
                                <span v-if="form.jawaban[currentQuestion.id] === parseOption(optRaw).teks" class="material-symbols-outlined text-primary text-[20px]">
                                    check_circle
                                </span>
                            </button>
                        </div>

                        <!-- 2. Tipe Teks -->
                        <div v-else-if="currentQuestion.tipe_jawaban === 'teks'">
                            <textarea
                                v-model="form.jawaban[currentQuestion.id]"
                                rows="3"
                                placeholder="Ketikkan jawaban Anda di sini..."
                                class="w-full p-4 border border-outline rounded-2xl bg-surface-container-low text-sm text-on-surface focus:outline-none focus:border-primary resize-none"
                            ></textarea>
                        </div>

                        <!-- 3. Tipe Angka -->
                        <div v-else-if="currentQuestion.tipe_jawaban === 'angka'">
                            <input
                                v-model.number="form.jawaban[currentQuestion.id]"
                                type="number"
                                placeholder="Masukkan angka..."
                                class="w-full h-12 px-4 border border-outline rounded-2xl bg-surface-container-low text-base text-on-surface focus:outline-none focus:border-primary"
                            />
                        </div>

                        <!-- 4. Tipe Foto -->
                        <div v-else-if="currentQuestion.tipe_jawaban === 'foto'" class="flex flex-col gap-3">
                            <div v-if="photoPreviews[currentQuestion.id]" class="relative w-full max-w-sm rounded-xl overflow-hidden border border-outline-variant">
                                <img :src="photoPreviews[currentQuestion.id]" class="w-full h-48 object-cover" />
                                <button type="button" @click="hapusFotoBaru(currentQuestion.id)" class="absolute top-2 right-2 bg-error text-on-error p-1 rounded-lg border-none cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            </div>

                            <div v-else-if="form.jawaban[currentQuestion.id]" class="relative w-full max-w-sm rounded-xl overflow-hidden border border-outline-variant">
                                <img :src="form.jawaban[currentQuestion.id]" class="w-full h-48 object-cover" />
                                <span class="absolute bottom-2 left-2 bg-black/70 text-white text-[10px] px-2 py-0.5 rounded font-mono">Foto Tersimpan</span>
                            </div>

                            <input type="file" accept="image/*" @change="(e) => handlePhotoUpload(currentQuestion!.id, e)" class="text-xs text-on-surface-variant file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary file:text-on-primary file:cursor-pointer" />
                        </div>
                    </div>

                    <!-- TOMBOL NAVIGASI BAWAH KARTU -->
                    <div class="flex items-center justify-between pt-6 border-t border-outline-variant mt-2">
                        <!-- Tombol Mundur -->
                        <button
                            type="button"
                            :disabled="questionHistory.length <= 1"
                            @click="handleBack"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-outline text-xs font-bold text-on-surface hover:bg-surface-container-high transition-colors disabled:opacity-30 cursor-pointer bg-transparent"
                        >
                            <span class="material-symbols-outlined text-sm">arrow_back</span>
                            <span>Sebelumnya</span>
                        </button>

                        <!-- Tombol Maju / Kirim -->
                        <div>
                            <!-- Jika Masih Ada Soal Berikutnya -->
                            <button
                                v-if="!isLastQuestion"
                                type="button"
                                :disabled="currentQuestion.wajib_diisi && isCurrentQuestionEmpty"
                                @click="handleNext"
                                class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-primary text-on-primary rounded-xl font-bold text-xs hover:bg-on-primary-fixed-variant transition-colors disabled:opacity-40 cursor-pointer border-none shadow-sm"
                            >
                                <span>Lanjutkan</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </button>

                            <!-- Jika Ini Soal Terakhir (Atau Opsi Langsung Selesai) -->
                            <button
                                v-else
                                type="button"
                                :disabled="form.processing || (currentQuestion.wajib_diisi && isCurrentQuestionEmpty)"
                                @click="submitJawaban"
                                class="inline-flex items-center gap-1.5 px-8 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition-colors disabled:opacity-40 cursor-pointer border-none shadow-sm"
                            >
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                <span>{{ form.processing ? 'Menyimpan...' : (activeSurvei.existingResponse ? 'Perbarui Jawaban' : 'Selesai & Kirim') }}</span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </AppLayout>
</template>
