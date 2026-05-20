<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ dataintegrasitpp: any, upts: any[] }>()

const form = useForm({
  _method: 'put',
  upt_id: String(props.dataintegrasitpp.upt_id) || '',
  tanggal_input: props.dataintegrasitpp.tanggal_input || '',
  tanggal_pelaksanaan: props.dataintegrasitpp.tanggal_pelaksanaan || '',
  nomor_sidang: props.dataintegrasitpp.nomor_sidang || '',
  jumlah_narapidana_sidang: props.dataintegrasitpp.jumlah_narapidana_sidang || '',
  rekomendiasi_sidang: props.dataintegrasitpp.rekomendiasi_sidang || '',
  permasalahan: props.dataintegrasitpp.permasalahan || '',
  upaya: props.dataintegrasitpp.upaya || '',
  berita_acara: null as File | null,
  absensi: null as File | null,
  dokumentasi_sidang: [] as File[], // <--- Array kosong
})

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files) form.dokumentasi_sidang = Array.from(target.files);
}

function submit() {
  form.post(`/admin/data-integrasi-tpps/${props.dataintegrasitpp.id}`, {
      forceFormData: true
  })
}
</script>

<template>
  <Head title="Input Sidang TPP" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Input Data Sidang TPP UPT</h1>
        <Link href="/admin/data-integrasi-tpps" class="btn-back border px-4 py-2 rounded-md hover:bg-accent text-sm">Kembali</Link>
      </div>

      <div class="rounded-xl border bg-card p-6 shadow-sm">
        <form @submit.prevent="submit" class="grid gap-6 md:grid-cols-2">
            
            <div class="space-y-2"><label class="text-sm font-medium">Tanggal Input</label><input v-model="form.tanggal_input" type="date" class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-medium">Lokasi UPT</label>
                <select v-model="form.upt_id" class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
            
            <div class="space-y-2"><label class="text-sm font-medium">Tanggal Pelaksanaan</label><input v-model="form.tanggal_pelaksanaan" type="date" class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-medium">Nomor Sidang</label><input v-model="form.nomor_sidang" type="text" placeholder="Contoh: W12.PAS.PAS.1-PK.05.09-XXX" class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold text-primary uppercase">Jumlah Narapidana Mengikuti Sidang</label><input v-model.number="form.jumlah_narapidana_sidang" type="number" min="0" required placeholder="Jumlah Orang" class="w-full h-10 border rounded-md px-3 bg-white focus:ring-primary" /></div>
            <div class="md:col-span-2 space-y-2"><label class="text-sm font-medium">Rekomendasi Sidang TPP</label><textarea v-model="form.rekomendiasi_sidang" rows="2" class="w-full border rounded-md px-3 py-2 bg-background"></textarea></div>
            <div class="space-y-2 md:col-span-2"><label class="text-sm font-medium text-red-600">Permasalahan yang Terjadi</label><textarea v-model="form.permasalahan" rows="2" class="w-full border rounded-md px-3 py-2 bg-background"></textarea></div>
            <div class="space-y-2"><label class="text-sm font-medium">Upaya yang Telah Dilakukan</label><textarea v-model="form.upaya" rows="2" class="w-full border rounded-md px-3 py-2 bg-background"></textarea></div>

            <div class="md:col-span-2 grid md:grid-cols-3 gap-4 p-4 bg-muted/30 rounded-lg border">
                <div class="space-y-2">
                    <label class="text-sm font-bold text-blue-600">Berita Acara (PDF max 5MB)</label>
                    <input type="file" accept="application/pdf" @input="form.berita_acara = $event.target.files[0]" class="text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-blue-100 file:text-blue-700 w-full" />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-bold text-indigo-600">Absensi (PDF max 5MB)</label>
                    <input type="file" accept="application/pdf" @input="form.absensi = $event.target.files[0]" class="text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-indigo-100 file:text-indigo-700 w-full" />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-bold text-primary">Dokumentasi (Pilih banyak foto sekaligus)</label>
                    <input type="file" multiple accept="image/*" @change="handleFileChange" class="w-full text-xs" />
                </div>
            </div>

            <div class="md:col-span-2 text-right mt-4"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-2 rounded-md font-bold shadow hover:opacity-90">Simpan Laporan TPP</button></div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>