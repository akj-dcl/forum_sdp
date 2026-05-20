<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps<{ dataperawatanharian: any, upts: any[] }>()

const form = useForm({
  _method: 'put',
  upt_id: String(props.dataperawatanharian.upt_id),
  tanggal_input: props.dataperawatanharian.tanggal_input,
  jumlah_penghuni_berobat: props.dataperawatanharian.jumlah_penghuni_berobat,
  jumlah_penghuni_dirawat: props.dataperawatanharian.jumlah_penghuni_dirawat,
  jumlah_wbp_meninggal: props.dataperawatanharian.jumlah_wbp_meninggal,
  
  jumlah_nama_berobatjalan: props.dataperawatanharian.jumlah_nama_berobatjalan,
  daftar_nama_berobatjalan: props.dataperawatanharian.daftar_nama_berobatjalan || [],
  
  jumlah_nama_rawatklinik: props.dataperawatanharian.jumlah_nama_rawatklinik,
  daftar_nama_rawatklinik: props.dataperawatanharian.daftar_nama_rawatklinik || [],
  
  dokumen: null as File | null,
})

watch(() => form.jumlah_nama_berobatjalan, (newVal) => {
    const count = Number(newVal) || 0;
    if (form.daftar_nama_berobatjalan.length < count) {
        while (form.daftar_nama_berobatjalan.length < count) form.daftar_nama_berobatjalan.push('');
    } else if (form.daftar_nama_berobatjalan.length > count) {
        form.daftar_nama_berobatjalan.splice(count);
    }
});

watch(() => form.jumlah_nama_rawatklinik, (newVal) => {
    const count = Number(newVal) || 0;
    if (form.daftar_nama_rawatklinik.length < count) {
        while (form.daftar_nama_rawatklinik.length < count) form.daftar_nama_rawatklinik.push('');
    } else if (form.daftar_nama_rawatklinik.length > count) {
        form.daftar_nama_rawatklinik.splice(count);
    }
});

function submit() {
  form.post(`/admin/data-perawatan-harians/${props.dataperawatanharian.id}`, { forceFormData: true })
}
</script>

<template>
  <Head title="Edit Perawatan Harian" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-semibold">Edit Perawatan Kesehatan Harian</h1>
        <Link href="/admin/data-perawatan-harians" class="border px-4 py-2 rounded-md hover:bg-accent text-sm">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Tanggal Laporan</label><input v-model="form.tanggal_input" type="date" class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-primary border-b pb-2">Data Kunjungan Umum</h3>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Jml Penghuni Berobat</label><input v-model.number="form.jumlah_penghuni_berobat" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Jml Penghuni Dirawat</label><input v-model.number="form.jumlah_penghuni_dirawat" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-red-500">Jml WBP Meninggal</label><input v-model.number="form.jumlah_wbp_meninggal" type="number" min="0" class="w-full h-9 border border-red-200 rounded px-3 text-sm bg-red-50 text-red-600 font-bold" /></div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <h3 class="font-bold text-orange-600 border-b pb-2 mb-4">Berobat Jalan</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold">Total WBP Berobat Jalan</label>
                    <input v-model.number="form.jumlah_nama_berobatjalan" type="number" min="0" class="w-full h-9 border border-orange-300 rounded px-3 text-sm font-bold text-orange-700 bg-orange-50" />
                </div>
                <div v-if="form.jumlah_nama_berobatjalan > 0" class="mt-4 space-y-2 p-3 bg-muted/30 rounded-lg border">
                    <p class="text-xs font-semibold text-muted-foreground">Daftar Nama WBP:</p>
                    <input v-for="(name, idx) in form.daftar_nama_berobatjalan" :key="'bj-'+idx" v-model="form.daftar_nama_berobatjalan[idx]" type="text" required class="w-full h-8 text-sm border rounded px-2 bg-background mb-2" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <h3 class="font-bold text-emerald-600 border-b pb-2 mb-4">Rawat Klinik</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold">Total WBP Rawat Klinik</label>
                    <input v-model.number="form.jumlah_nama_rawatklinik" type="number" min="0" class="w-full h-9 border border-emerald-300 rounded px-3 text-sm font-bold text-emerald-700 bg-emerald-50" />
                </div>
                <div v-if="form.jumlah_nama_rawatklinik > 0" class="mt-4 space-y-2 p-3 bg-muted/30 rounded-lg border">
                    <p class="text-xs font-semibold text-muted-foreground">Daftar Nama WBP:</p>
                    <input v-for="(name, idx) in form.daftar_nama_rawatklinik" :key="'rk-'+idx" v-model="form.daftar_nama_rawatklinik[idx]" type="text" required class="w-full h-8 text-sm border rounded px-2 bg-background mb-2" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-blue-50 p-6 shadow-sm border-blue-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-blue-800">Ganti Dokumen Laporan (PDF)</h3>
                <p class="text-xs text-blue-600 mt-1">Kosongkan jika file lama masih digunakan.</p>
            </div>
            <input type="file" accept="application/pdf" @input="form.dokumen = $event.target.files[0]" class="text-sm file:mr-3 file:rounded file:border-0 file:bg-blue-600 file:text-white file:px-4 file:py-2 hover:file:bg-blue-700" />
        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow hover:opacity-90">Simpan Perubahan</button></div>
      </form>
    </div>
  </AppLayout>
</template>