<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ dataperawatanbulanan: any, upts: any[] }>()

const form = useForm({
  _method: 'put',
  upt_id: String(props.dataperawatanbulanan.upt_id),
  tanggal_input: props.dataperawatanbulanan.tanggal_input,
  jumlah_penghuni_hiv: props.dataperawatanbulanan.jumlah_penghuni_hiv,
  jumlah_penghuni_tbc: props.dataperawatanbulanan.jumlah_penghuni_tbc,
  jumlah_penghuni_disabilitas: props.dataperawatanbulanan.jumlah_penghuni_disabilitas,
  jumlah_penghuni_jiwa: props.dataperawatanbulanan.jumlah_penghuni_jiwa,
  jumlah_penghuni_tidakmenular: props.dataperawatanbulanan.jumlah_penghuni_tidakmenular,
  jumlah_penghuni_menular: props.dataperawatanbulanan.jumlah_penghuni_menular,
  jenis_penyakit_dominan: props.dataperawatanbulanan.jenis_penyakit_dominan,
  jumlah_wbp_lansia: props.dataperawatanbulanan.jumlah_wbp_lansia,
  jumlah_wbp_berkepanjangan: props.dataperawatanbulanan.jumlah_wbp_berkepanjangan,
  jumlah_peserta_bpjs: props.dataperawatanbulanan.jumlah_peserta_bpjs,
  laporan: null as File | null,
})

function submit() {
  form.post(`/admin/data-perawatan-bulanans/${props.dataperawatanbulanan.id}`, { forceFormData: true })
}
</script>

<template>
  <Head title="Edit Perawatan Bulanan" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-semibold">Edit Data Perawatan Bulanan</h1>
        <Link href="/admin/data-perawatan-bulanans" class="border px-4 py-2 rounded-md hover:bg-accent text-sm">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Bulan Laporan</label><input v-model="form.tanggal_input" type="date" class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-red-600 border-b pb-2">Data Penyakit Menular & Khusus</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Penghuni HIV</label><input v-model.number="form.jumlah_penghuni_hiv" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Penghuni TBC</label><input v-model.number="form.jumlah_penghuni_tbc" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Penghuni Gangguan Jiwa</label><input v-model.number="form.jumlah_penghuni_jiwa" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Penyakit Menular Lainnya</label><input v-model.number="form.jumlah_penghuni_menular" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                </div>
                <div class="space-y-2 mt-4 pt-2 border-t">
                    <label class="text-xs font-bold text-primary">Jenis Penyakit Dominan</label>
                    <input v-model="form.jenis_penyakit_dominan" type="text" class="w-full h-9 border rounded px-3 text-sm bg-background" required />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-emerald-600 border-b pb-2">Kelompok Rentan & Data Umum</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Penghuni Disabilitas</label><input v-model.number="form.jumlah_penghuni_disabilitas" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">WBP Lansia (>60 Thn)</label><input v-model.number="form.jumlah_wbp_lansia" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Sakit Berkepanjangan</label><input v-model.number="form.jumlah_wbp_berkepanjangan" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Penyakit Tidak Menular</label><input v-model.number="form.jumlah_penghuni_tidakmenular" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                </div>
                <div class="space-y-2 mt-4 pt-2 border-t">
                    <label class="text-xs font-bold text-blue-600">Total Kepemilikan BPJS Kesehatan</label>
                    <input v-model.number="form.jumlah_peserta_bpjs" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-blue-50 font-bold text-blue-700" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-blue-50 p-6 shadow-sm border-blue-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-blue-800">Ganti Dokumen Laporan (PDF)</h3>
                <p class="text-xs text-blue-600 mt-1">Kosongkan jika tidak ingin mengganti file laporan yang lama.</p>
            </div>
            <input type="file" accept="application/pdf" @input="form.laporan = $event.target.files[0]" class="text-sm file:mr-3 file:rounded file:border-0 file:bg-blue-600 file:text-white file:px-4 file:py-2 hover:file:bg-blue-700 cursor-pointer" />
        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow hover:opacity-90">Simpan Perubahan</button></div>
      </form>
    </div>
  </AppLayout>
</template>