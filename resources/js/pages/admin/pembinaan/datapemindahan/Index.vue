<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ 
    datapemindahans: any, 
    upts: any[], 
    jenis_personels: any[],
    filters: { tanggal?: string, upt_id?: string } 
}>()

const filterUpt = ref(props.filters.upt_id || '')
const filterTanggal = ref(props.filters.tanggal || '')

const isModalOpen = ref(false)
const selectedData = ref<any | null>(null)

function showDetail(data: any) {
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
    [filterUpt, filterTanggal],
    debounce(([newUpt, newTanggal]) => {
        const params = new URLSearchParams()

        if (newUpt) params.append('upt_id', newUpt)
        if (newTanggal) params.append('tanggal', newTanggal)

        router.get(
            window.location.pathname,
            Object.fromEntries(params),
            {
                preserveState: true,
                preserveScroll: true,
                replace: true
            }
        )
    }, 300)
)

function destroyData(id: number) {
    if (!confirm('Yakin ingin menghapus data pemindahan ini?')) return

    router.delete(`/admin/data-pemindahans/${id}`)
}

function findPersonelName(id: string | number) {
    const p = props.jenis_personels.find(
        i => String(i.id) === String(id)
    )

    return p ? p.nama_personel : '-'
}

function countTotalPersonel(personelObj: any) {
    if (!personelObj) return 0

    return Object.values(personelObj).reduce(
        (a: any, b: any) => Number(a) + Number(b),
        0
    )
}
</script>

<template>
    <Head title="Data Pemindahan" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">

            <!-- HEADER -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Data Pemindahan WBP
                    </h1>

                    <p class="text-sm text-muted-foreground">
                        Kelola data pemindahan warga binaan pemasyarakatan.
                    </p>
                </div>

                <Link
                    href="/admin/data-pemindahans/create"
                    class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
                >
                    + Input Pemindahan
                </Link>
            </div>

            <!-- FILTER -->
            <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
                <input
                    v-model="filterTanggal"
                    type="date"
                    class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                />

                <select
                    v-model="filterUpt"
                    class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                >
                    <option value="">
                        Semua Lapas / UPT
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
                        <thead class="[&_tr]:border-b border-border">
                            <tr class="border-b transition-colors hover:bg-muted/50">
                                <th class="h-12 px-4 text-left font-medium">
                                    Tanggal
                                </th>

                                <th class="h-12 px-4 text-left font-medium">
                                    Jenis Pemindahan
                                </th>

                                <th class="h-12 px-4 text-left font-medium">
                                    Rute Pemindahan
                                </th>

                                <th class="h-12 px-4 text-center font-medium">
                                    Personel
                                </th>

                                <th class="h-12 px-4 text-center font-medium">
                                    Dokumen
                                </th>

                                <th class="h-12 px-4 text-right font-medium">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="[&_tr:last-child]:border-0">
                            <tr
                                v-for="d in datapemindahans.data"
                                :key="d.id"
                                class="border-b transition-colors hover:bg-muted/50"
                            >
                                <!-- TANGGAL -->
                                <td class="p-4 align-top">
                                    <div class="font-bold text-primary">
                                        {{ d.tanggal_pelaksanaan }}
                                    </div>

                                    <div class="text-xs text-muted-foreground mt-1">
                                        Input:
                                        {{ d.tanggal_input }}
                                    </div>
                                </td>

                                <!-- JENIS -->
                                <td class="p-4 align-top">
                                    <div class="flex flex-col gap-2">
                                        <span
                                            class="inline-flex w-fit items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700"
                                        >
                                            {{ d.jenis_pemindahan?.nama_pemindahan ?? '-' }}
                                        </span>

                                        <div
                                            v-if="d.detail_lain_lain"
                                            class="text-xs text-orange-600 font-medium"
                                        >
                                            Detail:
                                            {{ d.detail_lain_lain }}
                                        </div>

                                        <div
                                            v-if="d.jenis_kasus"
                                            class="text-xs text-muted-foreground"
                                        >
                                            Kasus:
                                            {{ d.jenis_kasus }}
                                        </div>
                                    </div>
                                </td>

                                <!-- RUTE -->
                                <td class="p-4 align-top">
                                    <div class="space-y-2">

                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-semibold text-orange-600">
                                                {{ d.upt_asal?.name ?? '-' }}
                                            </span>

                                            <span class="text-muted-foreground">
                                                ➜
                                            </span>

                                            <template v-if="d.is_luar_wilayah">
                                                <span class="font-semibold text-red-600">
                                                    {{ d.nama_upt_tujuan_luar }}
                                                </span>

                                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700">
                                                    LUAR WILAYAH
                                                </span>
                                            </template>

                                            <template v-else>
                                                <span class="font-semibold text-emerald-600">
                                                    {{ d.upt_tujuan?.name ?? '-' }}
                                                </span>
                                            </template>
                                        </div>

                                        <div class="text-xs text-muted-foreground">
                                            Pelapor:
                                            {{ d.upt?.name }}
                                        </div>
                                    </div>
                                </td>

                                <!-- PERSONEL -->
                                <td class="p-4 text-center align-top">
                                    <div
                                        class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700"
                                    >
                                        {{ countTotalPersonel(d.jumlah_personel) }} Orang
                                    </div>
                                </td>

                                <!-- DOKUMEN -->
                                <td class="p-4 align-top">
                                    <div class="flex flex-col gap-2 items-center">

                                        <a
                                            v-if="d.surat_usulan"
                                            :href="`/storage/${d.surat_usulan}`"
                                            target="_blank"
                                            class="text-xs text-blue-600 hover:underline"
                                        >
                                            Surat Usulan
                                        </a>

                                        <a
                                            v-if="d.surat_persetujuan"
                                            :href="`/storage/${d.surat_persetujuan}`"
                                            target="_blank"
                                            class="text-xs text-indigo-600 hover:underline"
                                        >
                                            Surat Persetujuan
                                        </a>

                                        <a
                                            v-if="d.file_laporan_pemindahan"
                                            :href="`/storage/${d.file_laporan_pemindahan}`"
                                            target="_blank"
                                            class="text-xs text-emerald-600 hover:underline"
                                        >
                                            File Laporan
                                        </a>

                                        <div
                                            v-if="d.foto_pemindahan?.length"
                                            class="text-[10px] text-muted-foreground"
                                        >
                                            {{ d.foto_pemindahan.length }} Foto
                                        </div>
                                    </div>
                                </td>

                                <!-- AKSI -->
                                <td class="p-4 text-right align-top">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            @click="showDetail(d)"
                                            class="inline-flex items-center rounded-md border border-input bg-background px-3 py-1 text-sm hover:bg-accent"
                                        >
                                            Lihat
                                        </button>

                                        <Link
                                            :href="`/admin/data-pemindahans/${d.id}/edit`"
                                            class="inline-flex items-center rounded-md border border-input bg-background px-3 py-1 text-sm hover:bg-accent"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            @click="destroyData(d.id)"
                                            class="inline-flex items-center rounded-md bg-red-500 px-3 py-1 text-sm text-white hover:bg-red-600"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!datapemindahans.data.length">
                                <td colspan="6" class="p-8 text-center text-muted-foreground">
                                    Tidak ada data pemindahan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINATION -->
            <div
                v-if="datapemindahans.links.length > 3"
                class="flex items-center justify-between mt-2"
            >
                <div class="text-sm text-muted-foreground">
                    Menampilkan
                    {{ datapemindahans.from ?? 0 }}
                    sampai
                    {{ datapemindahans.to ?? 0 }}
                    dari
                    {{ datapemindahans.total }}
                    data
                </div>

                <div class="flex gap-1">
                    <template
                        v-for="(link, key) in datapemindahans.links"
                        :key="key"
                    >
                        <div
                            v-if="link.url === null"
                            class="px-3 py-1 text-sm text-muted-foreground border border-transparent"
                            v-html="link.label"
                        />

                        <Link
                            v-else
                            :href="link.url"
                            preserve-state
                            preserve-scroll
                            class="px-3 py-1 text-sm rounded-md border transition-colors"
                            :class="
                                link.active
                                    ? 'bg-primary text-primary-foreground border-primary'
                                    : 'border-border bg-background hover:bg-accent hover:text-accent-foreground'
                            "
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- MODAL DETAIL -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            @click.self="closeModal"
        >
            <div class="w-full max-w-5xl max-h-[90vh] overflow-y-auto rounded-xl bg-card shadow-lg flex flex-col">

                <!-- HEADER -->
                <div class="flex justify-between items-center border-b p-6 sticky top-0 bg-card z-10">
                    <div>
                        <h2 class="text-lg font-semibold">
                            Detail Pemindahan
                        </h2>

                        <p class="text-sm text-muted-foreground">
                            {{ selectedData?.tanggal_pelaksanaan }}
                        </p>
                    </div>

                    <button
                        @click="closeModal"
                        class="text-2xl leading-none text-muted-foreground hover:text-foreground"
                    >
                        &times;
                    </button>
                </div>

                <!-- CONTENT -->
                <div
                    v-if="selectedData"
                    class="p-6 space-y-6"
                >
                    <!-- INFO -->
                    <div class="grid md:grid-cols-2 gap-4">

                        <div class="rounded-lg border p-4">
                            <p class="text-xs text-muted-foreground mb-1">
                                Jenis Pemindahan
                            </p>

                            <p class="font-bold">
                                {{ selectedData.jenis_pemindahan?.nama_pemindahan }}
                            </p>

                            <p
                                v-if="selectedData.detail_lain_lain"
                                class="text-sm text-orange-600 mt-2"
                            >
                                {{ selectedData.detail_lain_lain }}
                            </p>
                        </div>

                        <div class="rounded-lg border p-4">
                            <p class="text-xs text-muted-foreground mb-1">
                                Jenis Kasus
                            </p>

                            <p class="font-bold">
                                {{ selectedData.jenis_kasus || '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- RUTE -->
                    <div class="rounded-xl border p-5 bg-muted/30">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-4">

                            <div class="text-center">
                                <p class="text-xs text-muted-foreground uppercase">
                                    UPT Asal
                                </p>

                                <p class="font-bold text-orange-600 text-lg">
                                    {{ selectedData.upt_asal?.name }}
                                </p>
                            </div>

                            <div class="text-3xl text-muted-foreground">
                                ➜
                            </div>

                            <div class="text-center">
                                <p class="text-xs text-muted-foreground uppercase">
                                    UPT Tujuan
                                </p>

                                <template v-if="selectedData.is_luar_wilayah">
                                    <p class="font-bold text-red-600 text-lg">
                                        {{ selectedData.nama_upt_tujuan_luar }}
                                    </p>

                                    <span class="inline-flex mt-2 items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700">
                                        LUAR WILAYAH
                                    </span>
                                </template>

                                <template v-else>
                                    <p class="font-bold text-emerald-600 text-lg">
                                        {{ selectedData.upt_tujuan?.name }}
                                    </p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- PERSONEL -->
                    <div class="rounded-xl border bg-card p-5">
                        <h3 class="font-bold mb-4">
                            Rincian Personel
                            ({{ countTotalPersonel(selectedData.jumlah_personel) }} Orang)
                        </h3>

                        <div class="grid md:grid-cols-2 gap-3">
                            <div
                                v-for="(val, key) in selectedData.jumlah_personel"
                                :key="key"
                                class="flex items-center justify-between rounded-md border bg-muted/20 px-3 py-2"
                            >
                                <span class="text-sm">
                                    {{ findPersonelName(key) }}
                                </span>

                                <span class="font-bold">
                                    {{ val }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- DOKUMEN -->
                    <div class="grid md:grid-cols-2 gap-4">

                        <div class="rounded-xl border bg-blue-50 p-4 text-center">
                            <p class="text-xs font-bold text-blue-700 mb-3">
                                Surat Usulan
                            </p>

                            <a
                                v-if="selectedData.surat_usulan"
                                :href="`/storage/${selectedData.surat_usulan}`"
                                target="_blank"
                                class="inline-flex rounded-md bg-white px-4 py-2 text-xs shadow border hover:bg-blue-100"
                            >
                                Lihat Dokumen
                            </a>
                        </div>

                        <div class="rounded-xl border bg-indigo-50 p-4 text-center">
                            <p class="text-xs font-bold text-indigo-700 mb-3">
                                Surat Persetujuan
                            </p>

                            <a
                                v-if="selectedData.surat_persetujuan"
                                :href="`/storage/${selectedData.surat_persetujuan}`"
                                target="_blank"
                                class="inline-flex rounded-md bg-white px-4 py-2 text-xs shadow border hover:bg-indigo-100"
                            >
                                Lihat Dokumen
                            </a>
                        </div>

                        <!-- <div class="rounded-xl border bg-emerald-50 p-4 text-center">
                            <p class="text-xs font-bold text-emerald-700 mb-3">
                                File Laporan
                            </p>

                            <a
                                v-if="selectedData.file_laporan_pemindahan"
                                :href="`/storage/${selectedData.file_laporan_pemindahan}`"
                                target="_blank"
                                class="inline-flex rounded-md bg-white px-4 py-2 text-xs shadow border hover:bg-emerald-100"
                            >
                                Lihat File
                            </a>
                        </div> -->
                    </div>

                    <!-- FOTO -->
                    <div
                        v-if="selectedData.foto_pemindahan?.length"
                        class="rounded-xl border bg-card p-5"
                    >
                        <h3 class="font-bold mb-4">
                            Dokumentasi Foto
                        </h3>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <a
                                v-for="(foto, index) in selectedData.foto_pemindahan"
                                :key="index"
                                :href="`/storage/${foto}`"
                                target="_blank"
                                class="group overflow-hidden rounded-lg border bg-muted"
                            >
                                <img
                                    :src="`/storage/${foto}`"
                                    class="h-40 w-full object-cover transition group-hover:scale-105"
                                />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="border-t p-4 flex justify-end">
                    <button
                        @click="closeModal"
                        class="rounded-md bg-secondary px-6 py-2 text-sm hover:bg-secondary/80"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>