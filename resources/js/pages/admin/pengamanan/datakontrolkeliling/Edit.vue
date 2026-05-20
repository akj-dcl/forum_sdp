<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ datakontrolkeliling: any, upts: any[] }>()

const form = useForm({
  _method: 'put',
  upt_id: String(props.datakontrolkeliling.upt_id),
  tanggal_input: props.datakontrolkeliling.tanggal_input,
  waktu_kontrol: props.datakontrolkeliling.waktu_kontrol || '',
  nama_petugas_kontrol: props.datakontrolkeliling.nama_petugas_kontrol || '',
  area_kontrol: props.datakontrolkeliling.area_kontrol || '',
  hasil_kontrol: props.datakontrolkeliling.hasil_kontrol || '',
  tindak_lanjut: props.datakontrolkeliling.tindak_lanjut || '',
  rekomendasi: props.datakontrolkeliling.rekomendasi || '',
  dokumentasi_kontrol: null as File | null,
})

function submit() {
  form.post(`/admin/data-kontrol-kelilings/${props.datakontrolkeliling.id}`, { forceFormData: true })
}
</script>

<template>
  <Head title="Edit Kontrol Keliling" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-semibold">Edit Data Kontrol Keliling</h1>
        <Link href="/admin/data-kontrol-kelilings" class="border px-4 py-2 rounded-md hover:bg-accent text-sm">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Tanggal Pelaksanaan</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="rounded-xl border bg-card p-6 shadow-sm">
            <div class="grid md:grid-cols-2 gap-6 mb-6 border-b pb-6 border-border/50">
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Waktu Kontrol</label><input v-model="form.waktu_kontrol" type="time" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-primary uppercase">Nama Petugas Kontrol</label><input v-model="form.nama_petugas_kontrol" type="text" required class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Area yang Dikontrol</label><input v-model="form.area_kontrol" type="text" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Hasil Kontrol</label><input v-model="form.hasil_kontrol" type="text" class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2"><label class="text-xs font-bold text-orange-600 uppercase">Tindak Lanjut</label><textarea v-model="form.tindak_lanjut" rows="3" class="w-full border rounded-md px-3 py-2 text-sm bg-background"></textarea></div>
                <div class="space-y-2"><label class="text-xs font-bold text-emerald-600 uppercase">Rekomendasi</label><textarea v-model="form.rekomendasi" rows="3" class="w-full border rounded-md px-3 py-2 text-sm bg-background"></textarea></div>
            </div>
        </div>

        <div class="rounded-xl border bg-blue-50 p-6 shadow-sm border-blue-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-blue-800">Ganti Dokumentasi Laporan (PDF)</h3>
                <p class="text-xs text-blue-600 mt-1">Kosongkan saja jika file lama tidak ingin diganti.</p>
            </div>
            <input type="file" accept="application/pdf" @input="form.dokumentasi_kontrol = $event.target.files[0]" class="text-sm file:mr-3 file:rounded file:border-0 file:bg-blue-600 file:text-white file:px-4 file:py-2 hover:file:bg-blue-700 cursor-pointer" />
        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow hover:opacity-90">Simpan Perubahan</button></div>
      </form>
    </div>
  </AppLayout>
</template>