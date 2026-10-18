<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Props {
    roles?: string[];
    channels?: { id: number; name: string }[];
}

const props = withDefaults(defineProps<Props>(), {
    roles: () => [],
    channels: () => [],
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/dashboard' },
    { title: 'Master Survei', href: '/admin/master-survei' },
    { title: 'Buat Survei Baru', href: '/admin/master-survei/create' },
];

interface OptionItem {
    teks: string;
    aksi: 'lanjut' | 'selesai' | 'lompat';
    target_urutan: number | null;
}

interface QuestionItem {
    teks_pertanyaan: string;
    tipe_jawaban: 'teks' | 'pilihan' | 'angka' | 'foto';
    opsi_pilihan: OptionItem[];
    wajib_diisi: boolean;
}

interface FormState {
    judul: string;
    deskripsi: string;
    batas_pengisian: 'sekali' | 'berkali_kali';
    tanggal_mulai: string;
    tanggal_selesai: string;
    target_roles: string[];
    is_active: boolean;
    pertanyaans: QuestionItem[];
}

const form = useForm<FormState>({
    judul: '',
    channel_id: '',
    deskripsi: '',
    batas_pengisian: 'sekali',
    tanggal_mulai: '',
    tanggal_selesai: '',
    target_roles: [],
    is_active: true,
    pertanyaans: [
        {
            teks_pertanyaan: '',
            tipe_jawaban: 'pilihan',
            opsi_pilihan: [
                { teks: 'Berjalan', aksi: 'lanjut', target_urutan: null },
                { teks: 'Tidak Berjalan', aksi: 'selesai', target_urutan: null },
            ],
            wajib_diisi: true,
        },
    ],
});

const toggleRole = (role: string) => {
    const idx = form.target_roles.indexOf(role);
    if (idx > -1) form.target_roles.splice(idx, 1);
    else form.target_roles.push(role);
};

const toggleAllRoles = () => {
    if (form.target_roles.length === props.roles.length) form.target_roles = [];
    else form.target_roles = [...props.roles];
};

const tambahPertanyaan = () => {
    form.pertanyaans.push({
        teks_pertanyaan: '',
        tipe_jawaban: 'teks',
        opsi_pilihan: [
            { teks: '', aksi: 'lanjut', target_urutan: null },
        ],
        wajib_diisi: true,
    });
};

const hapusPertanyaan = (idx: number) => {
    if (form.pertanyaans.length > 1) {
        form.pertanyaans.splice(idx, 1);
    }
};

const tambahOpsi = (qIdx: number) => {
    form.pertanyaans[qIdx].opsi_pilihan.push({
        teks: '',
        aksi: 'lanjut',
        target_urutan: null,
    });
};

const hapusOpsi = (qIdx: number, oIdx: number) => {
    if (form.pertanyaans[qIdx].opsi_pilihan.length > 1) {
        form.pertanyaans[qIdx].opsi_pilihan.splice(oIdx, 1);
    }
};

const submit = () => {
    form.post('/admin/master-survei');
};




</script>

<template>
    <Head title="Buat Survei Baru" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto w-full py-6 flex flex-col gap-6">

            <div class="flex items-center justify-between select-none">
                <div>
                    <h1 class="text-2xl font-bold text-on-surface">Buat Survei Baru</h1>
                    <p class="text-xs text-on-surface-variant mt-1"></p>
                </div>
                <Link href="/admin/master-survei" class="px-4 py-2 border border-outline rounded-xl text-xs font-semibold text-on-surface hover:bg-surface-container-high transition-colors no-underline">
                    Kembali
                </Link>
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-6">

                <!-- 1. PENGATURAN UMUM -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-6 shadow-sm flex flex-col gap-5">
                    <h2 class="font-bold text-sm text-primary uppercase tracking-wider select-none flex items-center gap-1.5 border-b border-outline-variant pb-3">
                        <span class="material-symbols-outlined text-[18px]">settings</span>
                        1. Pengaturan & Aturan Akses Survei
                    </h2>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface">Judul Survei *</label>
                        <input v-model="form.judul" type="text" required placeholder="Contoh: Survei Layanan Lapas/Rutan" class="h-10 px-3.5 border border-outline rounded-xl bg-surface-container-low text-xs text-on-surface focus:outline-none focus:border-primary" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface">Deskripsi / Petunjuk</label>
                        <textarea v-model="form.deskripsi" rows="2" placeholder="Petunjuk pengerjaan..." class="p-3 border border-outline rounded-xl bg-surface-container-low text-xs text-on-surface focus:outline-none focus:border-primary resize-none"></textarea>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold text-on-surface">Batas Pengisian *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label :class="['flex items-center gap-2 p-3 rounded-xl border text-xs font-semibold cursor-pointer', form.batas_pengisian === 'sekali' ? 'border-primary bg-primary/10 text-primary' : 'border-outline bg-surface-container-low text-on-surface']">
                                <input type="radio" value="sekali" v-model="form.batas_pengisian" class="accent-primary" />
                                <div>
                                    <p class="font-bold">1 Kali Pengisian (Bisa Diedit)</p>
                                    <p class="text-[10px] text-on-surface-variant font-normal">Tiap akun hanya mengisi 1 kali.</p>
                                </div>
                            </label>
                            <label :class="['flex items-center gap-2 p-3 rounded-xl border text-xs font-semibold cursor-pointer', form.batas_pengisian === 'berkali_kali' ? 'border-primary bg-primary/10 text-primary' : 'border-outline bg-surface-container-low text-on-surface']">
                                <input type="radio" value="berkali_kali" v-model="form.batas_pengisian" class="accent-primary" />
                                <div>
                                    <p class="font-bold">Bebas / Berulang Kali</p>
                                    <p class="text-[10px] text-on-surface-variant font-normal">Bisa kirim respon baru berkali-kali.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-on-surface">Tanggal Mulai</label>
                            <input v-model="form.tanggal_mulai" type="date" class="h-10 px-3 border border-outline rounded-xl bg-surface-container-low text-xs text-on-surface focus:outline-none focus:border-primary" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-on-surface">Tanggal Berakhir (Deadline)</label>
                            <input v-model="form.tanggal_selesai" type="date" class="h-10 px-3 border border-outline rounded-xl bg-surface-container-low text-xs text-on-surface focus:outline-none focus:border-primary" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 pt-2 border-t border-outline-variant">
                        <div class="flex items-center justify-between select-none">
                            <label class="text-xs font-bold text-on-surface">Role yang Berhak Mengisi:</label>
                            <button type="button" @click="toggleAllRoles" class="text-[11px] font-bold text-primary hover:underline border-none bg-transparent cursor-pointer">
                                {{ form.target_roles.length === roles.length ? 'Kosongkan Semua' : 'Pilih Semua Role' }}
                            </button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-40 overflow-y-auto p-2 rounded-xl bg-surface-container-low border border-outline-variant">
                            <label v-for="r in roles" :key="r" class="flex items-center gap-2 p-1.5 rounded-lg text-xs font-medium cursor-pointer text-on-surface">
                                <input type="checkbox" :checked="form.target_roles.includes(r)" @change="toggleRole(r)" class="accent-primary rounded" />
                                <span class="truncate">{{ r }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface">Pilih Channel (Saluran) Penempatan Survei</label>
                            <select v-model="form.channel_id" class="h-10 px-3 border border-outline rounded-xl bg-surface-container-low text-xs text-on-surface focus:outline-none focus:border-primary">
                            <option value="">General Feed</option>
                                <option v-for="ch in channels" :key="ch.id" :value="ch.id">{{ ch.name }}</option>
                            </select>
                        </div>
                    </div>

                <!-- 2. BUTIR SOAL DENGAN SKIP LOGIC -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-6 shadow-sm flex flex-col gap-5">
                    <div class="flex items-center justify-between select-none border-b border-outline-variant pb-3">
                        <div>
                            <h2 class="font-bold text-sm text-primary uppercase tracking-wider flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">alt_route</span>
                                2. Pertanyaan & Alur ({{ form.pertanyaans.length }})
                            </h2>
                            <p class="text-[11px] text-on-surface-variant mt-0.5">Atur alur percabangan pada tiap pilihan ganda.</p>
                        </div>
                        <button type="button" @click="tambahPertanyaan" class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary/10 hover:bg-primary/20 text-primary rounded-lg text-xs font-bold transition-colors border-none cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            Tambah Soal
                        </button>
                    </div>

                    <div v-for="(q, qIdx) in form.pertanyaans" :key="qIdx" class="p-5 rounded-2xl bg-surface-container-low border border-outline-variant flex flex-col gap-4">
                        <div class="flex items-center justify-between border-b border-outline-variant pb-2">
                            <span class="text-xs font-bold text-primary">Soal #{{ qIdx + 1 }}</span>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-1.5 text-xs font-semibold cursor-pointer select-none">
                                    <input type="checkbox" v-model="q.wajib_diisi" class="accent-primary rounded" />
                                    <span>Wajib Diisi (Required)</span>
                                </label>
                                <button v-if="form.pertanyaans.length > 1" type="button" @click="hapusPertanyaan(qIdx)" class="text-error hover:bg-error/10 p-1 rounded-md border-none bg-transparent cursor-pointer flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2 flex flex-col gap-1">
                                <label class="text-[11px] font-bold text-on-surface">Teks Pertanyaan *</label>
                                <input v-model="q.teks_pertanyaan" type="text" required placeholder="Tuliskan pertanyaan..." class="h-10 px-3 border border-outline rounded-xl bg-surface-container-lowest text-xs text-on-surface focus:outline-none focus:border-primary" />
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] font-bold text-on-surface">Tipe Jawaban</label>
                                <select v-model="q.tipe_jawaban" class="h-10 px-3 border border-outline rounded-xl bg-surface-container-lowest text-xs text-on-surface focus:outline-none focus:border-primary cursor-pointer">
                                    <option value="pilihan">Pilihan Ganda</option>
                                    <option value="teks">Teks</option>
                                    <option value="angka">Angka</option>
                                    <option value="foto">Foto</option>
                                </select>
                            </div>
                        </div>

                        <!-- OPSI PILIHAN GANDA DENGAN AKSI PERCABANGAN -->
                        <div v-if="q.tipe_jawaban === 'pilihan'" class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant flex flex-col gap-3">
                            <label class="text-[11px] font-bold text-on-surface flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px] text-primary">fork_right</span>
                                Atur Opsi Jawaban & Alur Percabangan:
                            </label>

                            <div v-for="(opsi, oIdx) in q.opsi_pilihan" :key="oIdx" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/60">
                                <!-- Teks Pilihan -->
                                <input v-model="opsi.teks" type="text" required placeholder="Teks opsi (misal: Berjalan, Ya, Tidak)..." class="h-8 px-2.5 flex-1 border border-outline rounded-lg text-xs text-on-surface bg-surface-container-lowest focus:outline-none focus:border-primary" />

                                <!-- Pilihan Aksi Percabangan -->
                                <div class="flex items-center gap-2">
                                    <select v-model="opsi.aksi" class="h-8 px-2 border border-outline rounded-lg text-xs text-on-surface bg-surface-container-lowest focus:outline-none font-medium">
                                        <option value="lanjut">➡️ Lanjut Soal Berikutnya</option>
                                        <option value="selesai">🏁 Langsung Selesai (Soal Terakhir)</option>
                                        <option value="lompat">🔀 Lompat ke Soal Lain</option>
                                    </select>

                                    <!-- Target Soal Jika Lompat -->
                                    <select v-if="opsi.aksi === 'lompat'" v-model.number="opsi.target_urutan" class="h-8 px-2 border border-primary text-primary rounded-lg text-xs bg-surface-container-lowest font-bold">
                                        <option :value="null">Pilih Tujuan</option>
                                        <option v-for="(targetQ, targetIdx) in form.pertanyaans" :key="targetIdx" :value="targetIdx + 1" :disabled="targetIdx === qIdx">
                                            Ke Soal #{{ targetIdx + 1 }}
                                        </option>
                                    </select>

                                    <button type="button" @click="hapusOpsi(qIdx, oIdx)" class="text-error hover:bg-error/10 p-1 rounded-md border-none bg-transparent cursor-pointer">
                                        <span class="material-symbols-outlined text-[16px]">close</span>
                                    </button>
                                </div>
                            </div>

                            <button type="button" @click="tambahOpsi(qIdx)" class="self-start text-[11px] font-bold text-primary hover:underline border-none bg-transparent cursor-pointer mt-1">
                                + Tambah Pilihan Baru
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pb-12">
                    <Link href="/admin/master-survei" class="px-5 py-2.5 border border-outline rounded-xl text-xs font-semibold text-on-surface hover:bg-surface-container-high transition-colors no-underline">
                        Batal
                    </Link>
                    <button type="submit" :disabled="form.processing" class="px-8 py-2.5 bg-primary text-on-primary rounded-xl font-bold text-sm hover:bg-on-primary-fixed-variant transition-colors disabled:opacity-50 border-none cursor-pointer shadow-sm">
                        {{ form.processing ? 'Posting...' : 'Post Survei' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
