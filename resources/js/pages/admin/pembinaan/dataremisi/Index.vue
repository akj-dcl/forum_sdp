<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ 
    dataremisis: any, 
    upts: any[], 
    jenis_remisis: any[], 
    filters: { tanggal?: string, upt_id?: string } 
}>()

const page = usePage()

const filterUpt = ref(props.filters.upt_id || '')
const filterTanggal = ref(props.filters.tanggal || '')

// Modal
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

// Filter
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
                replace: true,
            }
        )
    }, 300)
)

function destroyData(id: number) {
    if (!confirm('Yakin ingin menghapus seluruh laporan remisi di tanggal ini?')) return

    router.delete(`/admin/data-remisis/${id}`)
}

// Helper cari nama remisi
function getNamaRemisi(id: string | number) {
    const jenis = props.jenis_remisis.find(
        j => String(j.id) === String(id)
    )

    return jenis ? jenis.nama_remisi : 'Tidak Diketahui'
}
</script>

<template>
    <Head title="Rekap Remisi Harian" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">

            <!-- HEADER -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Laporan Rekap Remisi Harian
                    </h1>

                    <p class="text-sm text-muted-foreground">
                        Kelola laporan jumlah usulan dan persetujuan remisi.
                    </p>
                </div>

                <Link
                    href="/admin/data-remisis/create"
                    class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
                >
                    + Input Laporan Remisi
                </Link>
            </div>

            <!-- FLASH -->
            <div
                v-if="page.props.flash?.success"
                class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600"
            >
                {{ page.props.flash.success }}
            </div>

            <!-- FILTER -->
            <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
                <input
                    v-model="filterTanggal"
                    type="date"
                    class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm cursor-pointer"
                />

                <select
                    v-model="filterUpt"
                    class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm cursor-pointer"
                >
                    <option value="">Semua Lapas / UPT</option>

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

                                <th class="h-12 px-4 text-left font-medium text-muted-foreground">
                                    Tanggal Laporan
                                </th>

                                <th class="h-12 px-4 text-left font-medium text-muted-foreground">
                                    Lokasi UPT
                                </th>

                                <th class="h-12 px-4 text-center font-medium text-muted-foreground">
                                    Jumlah Jenis Remisi
                                </th>

                                <th class="h-12 px-4 text-right font-medium text-muted-foreground">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="[&_tr:last-child]:border-0">
                            <tr
                                v-for="d in dataremisis.data"
                                :key="d.id"
                                class="border-b transition-colors hover:bg-muted/50"
                            >

                                <td class="p-4 font-bold text-primary">
                                    {{ d.tanggal_input }}
                                </td>

                                <td class="p-4 text-muted-foreground">
                                    {{ d.upt?.name ?? '-' }}
                                </td>

                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                        {{ d.detail_remisi?.length || 0 }} Tipe Remisi
                                    </span>
                                </td>

                                <td class="p-4 text-right align-middle">
                                    <div class="flex justify-end gap-2">

                                        <button
                                            @click="showDetail(d)"
                                            class="inline-flex items-center rounded-md text-sm font-medium border border-input bg-transparent hover:bg-blue-50 hover:text-blue-600 h-8 px-3"
                                        >
                                            Lihat Detail
                                        </button>

                                        <Link
                                            :href="`/admin/data-remisis/${d.id}/edit`"
                                            class="inline-flex items-center rounded-md text-sm font-medium border border-input bg-transparent hover:bg-accent h-8 px-3"
                                        >
                                            Edit Laporan
                                        </Link>

                                        <button
                                            @click="destroyData(d.id)"
                                            class="inline-flex items-center rounded-md text-sm font-medium bg-destructive text-white hover:bg-destructive/90 h-8 px-3"
                                        >
                                            Hapus
                                        </button>

                                    </div>
                                </td>

                            </tr>

                            <tr v-if="!dataremisis.data.length">
                                <td colspan="4" class="p-8 text-center text-muted-foreground">
                                    Tidak ada laporan remisi.
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>

            <!-- PAGINATION -->
            <div
                v-if="dataremisis.links.length > 3"
                class="flex items-center justify-between mt-2"
            >

                <div class="text-sm text-muted-foreground">
                    Menampilkan {{ dataremisis.from ?? 0 }}
                    sampai {{ dataremisis.to ?? 0 }}
                    dari {{ dataremisis.total }} data
                </div>

                <div class="flex gap-1">

                    <template
                        v-for="(link, key) in dataremisis.links"
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
                            :class="link.active
                                ? 'bg-primary text-primary-foreground border-primary'
                                : 'border-border bg-background hover:bg-accent hover:text-accent-foreground'"
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

            <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-xl bg-card shadow-lg flex flex-col">

                <!-- HEADER -->
                <div class="flex justify-between items-center border-b p-6 pb-4 sticky top-0 bg-card z-10">

                    <div>
                        <h2 class="text-lg font-semibold">
                            Rincian Remisi Harian
                        </h2>

                        <p class="text-sm text-muted-foreground">
                            {{ selectedData?.upt?.name }}
                            -
                            {{ selectedData?.tanggal_input }}
                        </p>
                    </div>

                    <button
                        @click="closeModal"
                        class="text-muted-foreground hover:text-foreground text-2xl leading-none"
                    >
                        &times;
                    </button>
                </div>

                <!-- CONTENT -->
                <div class="p-6 pt-2 space-y-4">

                    <div
                        v-for="(item, index) in selectedData?.detail_remisi"
                        :key="index"
                        class="bg-muted/30 border border-border p-4 rounded-lg flex flex-col gap-4"
                    >

                        <!-- TOP -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

                            <div class="flex-1">
                                <h3 class="font-bold text-primary text-base">
                                    {{ getNamaRemisi(item.jenis_remisi_id) }}
                                </h3>

                                <p class="text-sm text-muted-foreground mt-1">
                                    <span class="font-semibold text-foreground">
                                        Keterangan:
                                    </span>

                                    {{ item.keterangan || '-' }}
                                </p>
                            </div>

                            <!-- STAT -->
                            <div class="flex items-center gap-4 text-center bg-background p-2 rounded-md border shadow-sm shrink-0">

                                <div class="px-2">
                                    <p class="text-[10px] uppercase font-bold text-muted-foreground">
                                        Diusulkan
                                    </p>

                                    <p class="text-lg font-bold">
                                        {{ item.jumlah_diusulkan }}
                                    </p>
                                </div>

                                <div class="w-px h-8 bg-border"></div>

                                <div class="px-2">
                                    <p class="text-[10px] uppercase font-bold text-muted-foreground">
                                        Disetujui
                                    </p>

                                    <p class="text-lg font-bold text-emerald-600">
                                        {{ item.jumlah_remisi }}
                                    </p>
                                </div>

                                <div class="w-px h-8 bg-border"></div>

                                <div class="px-2">
                                    <p class="text-[10px] uppercase font-bold text-muted-foreground">
                                        Selisih
                                    </p>

                                    <p class="text-lg font-bold text-red-500">
                                        {{ item.jumlah_selisih }}
                                    </p>
                                </div>

                            </div>
                        </div>

                        <!-- FILE PDF -->
                        <div
                            v-if="item.sk_remisi"
                            class="pt-3 border-t"
                        >

                            <a
                                :href="`/view-file?path=${item.sk_remisi}`"
                                target="_blank"
                                class="inline-flex items-center rounded-md bg-blue-100 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-200 transition-colors"
                            >
                                📄 Lihat SK Remisi
                            </a>

                        </div>

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="border-t p-4 flex justify-end bg-card">
                    <button
                        @click="closeModal"
                        class="rounded bg-secondary px-6 py-2 text-sm hover:bg-secondary/80 font-medium"
                    >
                        Tutup Detail
                    </button>
                </div>

            </div>
        </div>
    </AppLayout>
</template>