<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ datapelanggarantatib: any, upts: any[] }>()

const form = useForm({
  _method: 'put',
  upt_id: String(props.datapelanggarantatib.upt_id),
  tanggal_input: props.datapelanggarantatib.tanggal_input,
  nama_wbp: props.datapelanggarantatib.nama_wbp,
  tanggal_pelanggaran: props.datapelanggarantatib.tanggal_pelanggaran || '',
  waktu_pelanggaran: props.datapelanggarantatib.waktu_pelanggaran || '',
  jenis_pelanggaran: props.datapelanggarantatib.jenis_pelanggaran || '',
  pasal_pelanggaran: props.datapelanggarantatib.pasal_pelanggaran || '',
  tindak_lanjut: props.datapelanggarantatib.tindak_lanjut || '',
  rekomendasi_pembinaan: props.datapelanggarantatib.rekomendasi_pembinaan || '',
})

function submit() {
  form.post(`/admin/data-pelanggaran-tatibs/${props.datapelanggarantatib.id}`)
}
</script>

<template>
  <Head title="Input Pelanggaran Tatib" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-red-600">Input Data Pelanggaran Tatib</h1>
            <p class="text-sm text-muted-foreground">Pencatatan pelanggaran tata tertib WBP dan pembinaannya.</p>
        </div>
        <Link href="/admin/data-pelanggaran-tatibs" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Tanggal Input (Pelaporan)</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="rounded-xl border border-red-200 bg-red-50/30 p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-red-800 border-b border-red-200 pb-2">PROFIL & DETAIL PELANGGARAN</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-primary">1. Nama WBP</label>
                    <input v-model="form.nama_wbp" type="text" placeholder="Nama lengkap WBP pelanggar..." required class="w-full h-9 border rounded px-3 text-sm bg-white" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">2. Tgl Pelanggaran</label><input v-model="form.tanggal_pelanggaran" type="date" class="w-full h-9 border rounded px-2 text-sm bg-white" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">2. Waktu Pelanggaran</label><input v-model="form.waktu_pelanggaran" type="time" class="w-full h-9 border rounded px-2 text-sm bg-white" /></div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground">3. Jenis Pelanggaran</label>
                    <input v-model="form.jenis_pelanggaran" type="text" placeholder="Contoh: Berkelahi, Membawa HP..." class="w-full h-9 border rounded-md px-3 text-sm bg-white" />
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-red-600">4. Pelanggaran Tatib Pasal..?</label>
                    <input v-model="form.pasal_pelanggaran" type="text" placeholder="Contoh: Pasal 4 huruf j Permenkumham 6/2013..." class="w-full h-9 border rounded-md px-3 text-sm bg-white" />
                </div>
            </div>

            <div class="rounded-xl border border-emerald-200 bg-emerald-50/30 p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-emerald-800 border-b border-emerald-200 pb-2">TINDAKAN & PEMBINAAN</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-blue-600">5. Tindak Lanjut Pelanggaran</label>
                    <textarea v-model="form.tindak_lanjut" rows="5" placeholder="Tindakan hukuman disiplin yang diberikan (sel pengasingan, pencabutan hak, dll)..." class="w-full border border-blue-200 rounded-md px-3 py-2 text-sm bg-white focus:ring-blue-500"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-emerald-600">6. Rekomendasi Pembinaan Lanjutan</label>
                    <textarea v-model="form.rekomendasi_pembinaan" rows="4" placeholder="Saran pembinaan kedepannya agar tidak mengulangi..." class="w-full border border-emerald-200 rounded-md px-3 py-2 text-sm bg-white focus:ring-emerald-500"></textarea>
                </div>
            </div>
        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-red-600 text-white px-8 py-3 rounded-md font-bold shadow-lg hover:bg-red-700">Simpan Laporan Pelanggaran</button></div>
      </form>
    </div>
  </AppLayout>
</template>