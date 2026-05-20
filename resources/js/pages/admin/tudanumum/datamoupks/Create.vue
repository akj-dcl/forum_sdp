<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{
    upts: any[],
    isUptUser: boolean
}>()

const form = useForm({
    kategori_pemrakarsa: 'UPT',

    upt_id: props.upts?.[0]?.id
        ? String(props.upts[0].id)
        : '',

    judul_kerjasama: '',
    jenis_kerjasama: '',
    instansi_kerjasama: '',

    // LINK
    draft_awal: '',

    masa_berlaku_mulai: '',
    masa_berlaku_selesai: '',

    // LINK
    file_mou_pks: '',
    dokumentasi_penandatanganan: '',
})

function submit() {
    form.post('/admin/data-mou-pks')
}
</script>

<template>
    <Head title="Buat MOU/PKS" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">

            <!-- HEADER -->
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-primary">
                        Buat Draft / Referensi MOU PKS
                    </h1>
                </div>

                <Link
                    href="/admin/data-mou-pks"
                    class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium"
                >
                    Kembali
                </Link>
            </div>

            <!-- FORM -->
            <form
                @submit.prevent="submit"
                class="space-y-6"
            >

                <!-- IDENTITAS -->
                <div class="rounded-xl border bg-card p-6 shadow-sm">

                    <h3 class="font-bold border-b pb-2 mb-4">
                        A. IDENTITAS KERJASAMA
                    </h3>

                    <div class="grid md:grid-cols-2 gap-6 mb-4">

                        <!-- PEMRAKARSA -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-muted-foreground uppercase">
                                Pemrakarsa (Pembuat)
                            </label>

                            <select
                                v-model="form.kategori_pemrakarsa"
                                class="w-full h-10 border rounded-md px-3 bg-blue-50 font-bold text-blue-800"
                            >
                                <option value="UPT">
                                    UPT (Proses Review)
                                </option>

                                <template v-if="!isUptUser">
                                    <option value="KANWIL">
                                        Kanwil (Langsung Referensi Final)
                                    </option>

                                    <option value="DITJENPAS">
                                        Ditjenpas (Langsung Referensi Final)
                                    </option>
                                </template>
                            </select>
                        </div>

                        <!-- UPT -->
                        <div
                            v-if="form.kategori_pemrakarsa === 'UPT'"
                            class="space-y-2"
                        >
                            <label class="text-xs font-bold text-muted-foreground uppercase">
                                Lokasi UPT
                            </label>

                            <select
                                v-model="form.upt_id"
                                required
                                class="w-full h-10 border rounded-md px-3 bg-white"
                            >
                                <option
                                    v-for="u in upts"
                                    :key="u.id"
                                    :value="String(u.id)"
                                >
                                    {{ u.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- FORM UTAMA -->
                    <div class="space-y-4">

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-muted-foreground uppercase">
                                Judul Kerjasama
                            </label>

                            <input
                                v-model="form.judul_kerjasama"
                                type="text"
                                placeholder="Contoh: Pembinaan Kemandirian WBP..."
                                required
                                class="w-full h-10 border rounded px-3 text-sm"
                            />
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">

                            <!-- JENIS -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-muted-foreground uppercase">
                                    Jenis Kerjasama
                                </label>

                                <select
                                    v-model="form.jenis_kerjasama"
                                    required
                                    class="w-full h-10 border rounded px-3 text-sm bg-white"
                                >
                                    <option value="">
                                        -- Pilih Jenis --
                                    </option>

                                    <option value="PERJANJIAN KERJASAMA (PKS)">
                                        PERJANJIAN KERJASAMA (PKS)
                                    </option>

                                    <option value="NOTA KESEPAHAMAN">
                                        NOTA KESEPAHAMAN
                                    </option>
                                </select>
                            </div>

                            <!-- MITRA -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-muted-foreground uppercase">
                                    Instansi Mitra
                                </label>

                                <input
                                    v-model="form.instansi_kerjasama"
                                    type="text"
                                    placeholder="Dinas / LPK / Polres..."
                                    required
                                    class="w-full h-10 border rounded px-3 text-sm"
                                />
                            </div>

                        </div>
                    </div>
                </div>

                <!-- UPT -->
                <div
                    v-if="form.kategori_pemrakarsa === 'UPT'"
                    class="rounded-xl border border-orange-200 bg-orange-50/50 p-6 shadow-sm"
                >

                    <h3 class="font-bold text-orange-800 border-b border-orange-200 pb-2 mb-4">
                        B. TAHAPAN DRAFT AWAL
                    </h3>

                    <div class="space-y-2">

                        <label class="text-xs font-bold text-orange-800 uppercase">
                            Link Draft Awal UPT
                        </label>

                        <input
                            v-model="form.draft_awal"
                            type="url"
                            placeholder="https://drive.google.com/..."
                            required
                            class="w-full bg-white border rounded p-2 text-sm"
                        />

                        <p class="text-xs text-muted-foreground mt-1">
                            Upload file ke Google Drive / OneDrive lalu tempel link di sini.
                        </p>
                    </div>
                </div>

                <!-- REFERENSI -->
                <div
                    v-else
                    class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-6 shadow-sm"
                >

                    <h3 class="font-bold text-emerald-800 border-b border-emerald-200 pb-2 mb-4">
                        B. DATA MOU FINAL (REFERENSI)
                    </h3>

                    <div class="grid md:grid-cols-2 gap-6 mb-4">

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-muted-foreground uppercase">
                                Masa Berlaku Mulai
                            </label>

                            <input
                                v-model="form.masa_berlaku_mulai"
                                type="date"
                                required
                                class="w-full h-10 border rounded px-3 text-sm bg-white"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-muted-foreground uppercase">
                                Masa Berlaku Selesai
                            </label>

                            <input
                                v-model="form.masa_berlaku_selesai"
                                type="date"
                                required
                                class="w-full h-10 border rounded px-3 text-sm bg-white"
                            />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">

                        <!-- LINK DOKUMEN -->
                        <div class="space-y-2">

                            <label class="text-xs font-bold text-emerald-700 uppercase">
                                Link Dokumen MOU/PKS
                            </label>

                            <input
                                v-model="form.file_mou_pks"
                                type="url"
                                placeholder="https://drive.google.com/..."
                                required
                                class="w-full bg-white border rounded p-2 text-sm"
                            />
                        </div>

                        <!-- LINK DOKUMENTASI -->
                        <div class="space-y-2">

                            <label class="text-xs font-bold text-indigo-700 uppercase">
                                Link Dokumentasi Penandatanganan
                            </label>

                            <input
                                v-model="form.dokumentasi_penandatanganan"
                                type="url"
                                placeholder="https://drive.google.com/..."
                                class="w-full bg-white border rounded p-2 text-sm"
                            />
                        </div>

                    </div>
                </div>

                <!-- SUBMIT -->
                <div class="text-right">

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow-lg hover:opacity-90"
                    >
                        Simpan Data MOU
                    </button>
                </div>

            </form>
        </div>
    </AppLayout>
</template>