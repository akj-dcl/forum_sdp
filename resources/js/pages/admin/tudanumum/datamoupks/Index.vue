<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

interface Upt {
    id: number
    name: string
}

interface DataMouPks {
    id: number
    kategori_pemrakarsa: string
    judul_kerjasama: string
    instansi_kerjasama: string
    jenis_kerjasama: string
    status_tahapan: string | null
    masa_berlaku_mulai: string | null
    masa_berlaku_selesai: string | null
    file_mou_pks: string | null
    dokumentasi_penandatanganan: string | null
    upt?: Upt | null
}

const props = defineProps<{
    datamoupks: {
        data: DataMouPks[]
        links: any[]
    }
    upts: Upt[]
    filters: {
        upt_id?: string
    }
    isUptUser: boolean
}>()

const filterUpt = ref(props.filters.upt_id || '')

const isModalOpen = ref(false)
const selectedData = ref<DataMouPks | null>(null)

function showDetail(data: DataMouPks) {
    selectedData.value = data
    isModalOpen.value = true
}

function closeModal() {
    isModalOpen.value = false

    setTimeout(() => {
        selectedData.value = null
    }, 200)
}

watch(
    filterUpt,
    debounce((newUpt: string) => {
        router.get(
            window.location.pathname,
            {
                upt_id: newUpt,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        )
    }, 300)
)

function destroyData(id: number) {
    if (!confirm('Yakin ingin menghapus seluruh data & riwayat MOU ini?')) {
        return
    }

    router.delete(`/admin/data-mou-pks/${id}`)
}

function isSelesai(status: string | null) {
    if (!status) return false

    return status.toLowerCase().includes('selesai')
}

function fileUrl(path: string | null) {
    if (!path) return '#'

    return `/storage/${path}`
}
</script>

<template>
    <Head title="MOU & PKS" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">

            <!-- HEADER -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-primary">
                        Data MOU & PKS
                    </h1>
                </div>

                <Link
                    href="/admin/data-mou-pks/create"
                    class="inline-flex items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-bold shadow hover:bg-primary/90"
                >
                    + Buat MOU / Upload Referensi
                </Link>
            </div>

            <!-- FILTER -->
            <div class="grid gap-3 md:grid-cols-4">
                <select
                    v-model="filterUpt"
                    class="col-span-1 md:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm cursor-pointer"
                >
                    <option value="">
                        Semua Instansi (UPT & Kanwil)
                    </option>

                    <option
                        v-for="upt in upts"
                        :key="upt.id"
                        :value="String(upt.id)"
                    >
                        {{ upt.name }}
                    </option>
                </select>
            </div>

            <!-- TABLE -->
            <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm overflow-hidden">
                <div class="relative w-full overflow-auto">

                    <table class="w-full caption-bottom text-sm">
                        <thead class="bg-muted/50 border-b border-border">
                            <tr>
                                <th class="h-12 px-4 text-left font-bold text-muted-foreground w-20">
                                    Tingkat
                                </th>

                                <th class="h-12 px-4 text-left font-bold text-primary">
                                    Judul Kerjasama
                                </th>

                                <th class="h-12 px-4 text-left font-bold text-muted-foreground">
                                    Instansi Mitra
                                </th>

                                <th class="h-12 px-4 text-center font-bold text-muted-foreground">
                                    Status
                                </th>

                                <th class="h-12 px-4 text-right font-bold text-muted-foreground">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="d in datamoupks.data"
                                :key="d.id"
                                class="border-b hover:bg-muted/30"
                            >
                                <!-- TINGKAT -->
                                <td class="p-4">
                                    <span
                                        class="font-black"
                                        :class="
                                            d.kategori_pemrakarsa === 'UPT'
                                                ? 'text-blue-600'
                                                : 'text-purple-600'
                                        "
                                    >
                                        {{ d.kategori_pemrakarsa }}
                                    </span>
                                </td>

                                <!-- JUDUL -->
                                <td
                                    class="p-4 font-bold max-w-xs truncate"
                                    :title="d.judul_kerjasama"
                                >
                                    {{ d.judul_kerjasama }}
                                </td>

                                <!-- INSTANSI -->
                                <td
                                    class="p-4 max-w-[220px] truncate"
                                    :title="d.instansi_kerjasama"
                                >
                                    {{ d.instansi_kerjasama }}
                                </td>

                                <!-- STATUS -->
                                <td class="p-4 text-center">
                                    <span
                                        v-if="isSelesai(d.status_tahapan)"
                                        class="bg-emerald-100 text-emerald-800 px-2 py-1 rounded text-[10px] font-bold uppercase"
                                    >
                                        ✅ {{ d.status_tahapan }}
                                    </span>

                                    <span
                                        v-else
                                        class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-[10px] font-bold uppercase border border-orange-200"
                                    >
                                        ⏳ {{ d.status_tahapan || 'Proses' }}
                                    </span>
                                </td>

                                <!-- AKSI -->
                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-2 flex-wrap">

                                        <button
                                            @click="showDetail(d)"
                                            class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium"
                                        >
                                            Buka Berkas
                                        </button>

                                        <template v-if="!isUptUser || d.kategori_pemrakarsa === 'UPT'">

                                            <Link
                                                :href="`/admin/data-mou-pks/${d.id}/edit`"
                                                class="px-3 py-1 bg-primary text-white rounded-md hover:bg-primary/90 text-xs font-bold"
                                            >
                                                Kelola / Edit
                                            </Link>

                                            <button
                                                @click="destroyData(d.id)"
                                                class="px-3 py-1 bg-red-600 text-white rounded-md text-xs font-bold hover:bg-red-700"
                                            >
                                                Hapus
                                            </button>

                                        </template>
                                    </div>
                                </td>
                            </tr>

                            <!-- EMPTY -->
                            <tr v-if="!datamoupks.data.length">
                                <td
                                    colspan="5"
                                    class="p-8 text-center text-muted-foreground italic"
                                >
                                    Belum ada data MOU/PKS.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div
                    v-if="datamoupks.links?.length > 3"
                    class="flex flex-wrap items-center justify-center gap-2 p-4 border-t"
                >
                    <template
                        v-for="(link, index) in datamoupks.links"
                        :key="index"
                    >
                        <button
                            v-if="link.url"
                            @click="router.visit(link.url)"
                            v-html="link.label"
                            class="px-3 py-1 rounded border text-sm"
                            :class="link.active
                                ? 'bg-primary text-white'
                                : 'hover:bg-muted'"
                        />

                        <span
                            v-else
                            v-html="link.label"
                            class="px-3 py-1 text-sm text-muted-foreground"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            @click.self="closeModal"
        >
            <div class="w-full max-w-3xl rounded-xl bg-card shadow-lg flex flex-col max-h-[90vh] overflow-hidden border border-border">

                <!-- HEADER -->
                <div class="flex justify-between items-center border-b border-border p-5 bg-muted/20">
                    <div>
                        <h2 class="text-lg font-black text-primary">
                            Berkas MOU / PKS
                        </h2>

                        <p class="text-xs text-muted-foreground">
                            Pemrakarsa:
                            <span class="font-bold">
                                {{
                                    selectedData?.kategori_pemrakarsa === 'UPT'
                                        ? selectedData?.upt?.name
                                        : selectedData?.kategori_pemrakarsa
                                }}
                            </span>
                        </p>
                    </div>

                    <button
                        @click="closeModal"
                        class="text-2xl leading-none text-muted-foreground hover:text-red-500"
                    >
                        &times;
                    </button>
                </div>

                <!-- CONTENT -->
                <div class="p-6 space-y-6 overflow-y-auto">

                    <div class="text-center pb-4 border-b border-border">
                        <h3 class="text-xl font-black text-foreground leading-tight px-4">
                            {{ selectedData?.judul_kerjasama }}
                        </h3>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">

                        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                            <p class="text-[10px] font-bold text-blue-600 uppercase mb-1 tracking-wider">
                                Instansi Mitra
                            </p>

                            <p class="text-sm font-bold text-blue-900 leading-snug">
                                {{ selectedData?.instansi_kerjasama }}
                            </p>
                        </div>

                        <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                            <p class="text-[10px] font-bold text-indigo-600 uppercase mb-1 tracking-wider">
                                Jenis Kerjasama
                            </p>

                            <p class="text-sm font-bold text-indigo-900 leading-snug">
                                {{ selectedData?.jenis_kerjasama }}
                            </p>
                        </div>
                    </div>

                    <!-- SELESAI -->
                    <div
                        v-if="isSelesai(selectedData?.status_tahapan || null)"
                        class="space-y-6 pt-2"
                    >
                        <div class="flex justify-center items-center bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">

                            <div class="text-center px-8 border-r border-emerald-200 w-1/2">
                                <p class="text-[10px] font-bold text-emerald-600 uppercase mb-1">
                                    Berlaku Mulai
                                </p>

                                <p class="text-lg font-black">
                                    {{ selectedData?.masa_berlaku_mulai || '-' }}
                                </p>
                            </div>

                            <div class="text-center px-8 w-1/2">
                                <p class="text-[10px] font-bold text-emerald-600 uppercase mb-1">
                                    Berlaku Sampai
                                </p>

                                <p class="text-lg font-black">
                                    {{ selectedData?.masa_berlaku_selesai || '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">

                            <!-- FILE -->
                            <div class="bg-card p-5 rounded-xl flex flex-col items-center text-center border shadow-sm">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mb-3 text-xl">
                                    📜
                                </div>

                                <h4 class="font-bold text-sm text-blue-800 mb-3">
                                    Dokumen MOU Resmi
                                </h4>

                                <a
                                    v-if="selectedData?.file_mou_pks"
                                    :href="fileUrl(selectedData.file_mou_pks)"
                                    target="_blank"
                                    class="bg-blue-600 text-white px-6 py-2 rounded-md text-sm font-bold shadow hover:bg-blue-700 w-full transition-colors"
                                >
                                    Buka Dokumen
                                </a>

                                <span
                                    v-else
                                    class="text-xs text-muted-foreground italic"
                                >
                                    Dokumen belum diunggah.
                                </span>
                            </div>

                            <!-- DOKUMENTASI -->
                            <div class="bg-card p-5 rounded-xl flex flex-col items-center text-center border shadow-sm">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mb-3 text-xl">
                                    📸
                                </div>

                                <h4 class="font-bold text-sm text-indigo-800 mb-3">
                                    Dokumentasi Kegiatan
                                </h4>

                                <a
                                    v-if="selectedData?.dokumentasi_penandatanganan"
                                    :href="fileUrl(selectedData.dokumentasi_penandatanganan)"
                                    target="_blank"
                                    class="bg-indigo-600 text-white px-6 py-2 rounded-md text-sm font-bold shadow hover:bg-indigo-700 w-full transition-colors"
                                >
                                    Lihat Dokumentasi
                                </a>

                                <span
                                    v-else
                                    class="text-xs text-muted-foreground italic"
                                >
                                    Dokumentasi belum diunggah.
                                </span>
                            </div>

                        </div>
                    </div>

                    <!-- PROSES -->
                    <div
                        v-else
                        class="text-center py-8 bg-orange-50 rounded-xl border border-orange-200 mt-4"
                    >
                        <div class="text-4xl mb-3">
                            ⏳
                        </div>

                        <h3 class="font-black text-orange-800 text-lg mb-1">
                            MOU Masih Dalam Proses
                        </h3>

                        <p class="text-sm text-muted-foreground">
                            Status saat ini:
                            <b>{{ selectedData?.status_tahapan || 'Proses' }}</b>

                            <br>

                            Silakan klik tombol
                            <b>Kelola / Edit</b>
                            untuk melihat riwayat draft.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>