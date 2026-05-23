```vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
import { onMounted, computed } from 'vue'

const props = defineProps<{ 
    upts_lokasi: any[], 
    semua_upt: any[], 
    jenis_pemindahans: any[], 
    jenis_personels: any[] 
}>()

const form = useForm({
    upt_id: props.upts_lokasi?.[0]?.id
        ? String(props.upts_lokasi[0].id)
        : '',

    tanggal_input: new Date().toISOString().split('T')[0],

    tanggal_pelaksanaan: '',

    upt_asal_id: '',

    upt_tujuan_id: '',

    is_keluar_wilayah: false,

    tujuan_luar_wilayah: '',

    jenis_pemindahan_id: '',

    detail_lain_lain: '',

    jenis_kasus: '',

    surat_usulan: null as File | null,

    surat_persetujuan: null as File | null,

    laporan_pemindahan: null as File | null,

    foto_pemindahan: [] as File[],

    jumlah_personel: {} as Record<number, number>
})

onMounted(() => {

    props.jenis_personels?.forEach(item => {

        form.jumlah_personel[item.id] = 0
    })
})

const isLainLain = computed(() => {

    const selected = props.jenis_pemindahans.find(
        item => String(item.id) === String(form.jenis_pemindahan_id)
    )

    return selected?.nama_pemindahan === 'Lain - Lain'
})

function submit() {
    form.post('/admin/data-pemindahans', {
        forceFormData: true, // Mengunci data sebagai berkas objek asli
        preserveScroll: true
    })
}
</script>

<template>
    <Head title="Input Data Pemindahan" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">

            <div class="flex items-center justify-between mb-2">
                <div>
                    <h1 class="text-2xl font-semibold">
                        Input Pemindahan WBP
                    </h1>

                    <p class="text-sm text-muted-foreground">
                        Input data pemindahan warga binaan.
                    </p>
                </div>

                <Link
                    href="/admin/data-pemindahans"
                    class="border px-4 py-2 rounded-md hover:bg-accent text-sm"
                >
                    Kembali
                </Link>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-6"
            >

                <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">

                    <div class="space-y-2">
                        <label class="text-sm font-bold">
                            Tanggal Input
                        </label>

                        <input
                            v-model="form.tanggal_input"
                            type="date"
                            class="w-full h-10 border rounded-md px-3 bg-background"
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold">
                            Lokasi Pelapor (UPT)
                        </label>

                        <select
                            v-model="form.upt_id"
                            class="w-full h-10 border rounded-md px-3 bg-background"
                        >
                            <option
                                v-for="u in upts_lokasi"
                                :key="u.id"
                                :value="String(u.id)"
                            >
                                {{ u.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">

                    <div class="space-y-2">
                        <label class="text-sm font-bold">
                            Tanggal Pelaksanaan
                        </label>

                        <input
                            v-model="form.tanggal_pelaksanaan"
                            type="date"
                            class="w-full h-10 border rounded-md px-3 bg-background"
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-primary">
                            Jenis Pemindahan
                        </label>

                        <select
                            v-model="form.jenis_pemindahan_id"
                            class="w-full h-10 border rounded-md px-3 bg-background"
                        >
                            <option value="">
                                -- Pilih Jenis --
                            </option>

                            <option
                                v-for="jp in jenis_pemindahans"
                                :key="jp.id"
                                :value="String(jp.id)"
                            >
                                {{ jp.nama_pemindahan }}
                            </option>
                        </select>
                    </div>

                    <div
                        v-if="isLainLain"
                        class="md:col-span-2 space-y-2"
                    >
                        <label class="text-sm font-bold text-orange-600">
                            Detail Lain-Lain
                        </label>

                        <textarea
                            v-model="form.detail_lain_lain"
                            rows="3"
                            class="w-full border rounded-md px-3 py-2 bg-background"
                            placeholder="Jelaskan detail pemindahan..."
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold">
                            Jenis Kasus
                        </label>

                        <input
                            v-model="form.jenis_kasus"
                            type="text"
                            placeholder="Contoh: Narkotika"
                            class="w-full h-10 border rounded-md px-3 bg-background"
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold">
                            Pemindahan Keluar Wilayah?
                        </label>

                        <select
                            v-model="form.is_keluar_wilayah"
                            class="w-full h-10 border rounded-md px-3 bg-background"
                        >
                            <option :value="false">
                                Tidak
                            </option>

                            <option :value="true">
                                Ya
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-orange-600">
                            UPT Asal
                        </label>

                        <select
                            v-model="form.upt_asal_id"
                            class="w-full h-10 border rounded-md px-3 bg-background"
                        >
                            <option value="">
                                -- Pilih UPT Asal --
                            </option>

                            <option
                                v-for="u in semua_upt"
                                :key="u.id"
                                :value="String(u.id)"
                            >
                                {{ u.name }}
                            </option>
                        </select>
                    </div>

                    <div
                        v-if="!form.is_keluar_wilayah"
                        class="space-y-2"
                    >
                        <label class="text-sm font-bold text-emerald-600">
                            UPT Tujuan
                        </label>

                        <select
                            v-model="form.upt_tujuan_id"
                            class="w-full h-10 border rounded-md px-3 bg-background"
                        >
                            <option value="">
                                -- Pilih UPT Tujuan --
                            </option>

                            <option
                                v-for="u in semua_upt"
                                :key="u.id"
                                :value="String(u.id)"
                            >
                                {{ u.name }}
                            </option>
                        </select>
                    </div>

                    <div
                        v-else
                        class="space-y-2"
                    >
                        <label class="text-sm font-bold text-red-600">
                            Tujuan Luar Wilayah
                        </label>

                        <input
                            v-model="form.tujuan_luar_wilayah"
                            type="text"
                            placeholder="Contoh: Lapas Surabaya"
                            class="w-full h-10 border rounded-md px-3 bg-background"
                        />
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">

                    <div class="rounded-xl border bg-card p-6 shadow-sm">

                        <h3 class="font-bold text-lg mb-4 border-b pb-2">
                            Jumlah & Jenis Personel
                        </h3>

                        <div class="space-y-3">

                            <div
                                v-for="item in jenis_personels"
                                :key="item.id"
                                class="flex justify-between items-center bg-muted/30 p-2 rounded-md"
                            >
                                <label class="text-sm font-medium">
                                    {{ item.nama_personel }}
                                </label>

                                <input
                                    v-model.number="form.jumlah_personel[item.id]"
                                    type="number"
                                    min="0"
                                    class="w-20 rounded-md border border-input px-3 py-1 text-sm text-right bg-background"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-card p-6 shadow-sm space-y-6">

                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <label class="text-sm font-bold text-blue-700 block mb-2">
                                Upload Surat Usulan
                            </label>

                            <input
                                type="file"
                                accept="application/pdf"
                                @change="form.surat_usulan = $event.target.files[0]"
                                class="text-sm w-full"
                            />
                        </div>

                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                            <label class="text-sm font-bold text-indigo-700 block mb-2">
                                Upload Surat Persetujuan
                            </label>

                            <input
                                type="file"
                                accept="application/pdf"
                                @change="form.surat_persetujuan = $event.target.files[0]"
                                class="text-sm w-full"
                            />
                        </div>

                        <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100">
                            <label class="text-sm font-bold text-emerald-700 block mb-2">
                                Upload Laporan Pemindahan
                            </label>

                            <input
                                type="file"
                                accept="application/pdf"
                                @change="form.laporan_pemindahan = $event.target.files[0]"
                                class="text-sm w-full"
                            />
                        </div>

                        <div class="bg-orange-50 p-4 rounded-lg border border-orange-100">
                            <label class="text-sm font-bold text-orange-700 block mb-2">
                                Upload Dokumentasi Foto
                            </label>

                            <input
                                type="file"
                                multiple
                                accept="image/*"
                                @change="form.foto_pemindahan = Array.from($event.target.files)"
                                class="text-sm w-full"
                            />
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow hover:opacity-90"
                    >
                        Simpan Data Pemindahan
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
```
