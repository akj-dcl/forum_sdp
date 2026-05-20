<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ datainformasiintelejen: any, upts: any[] }>()

const form = useForm({
  _method: 'put',
  upt_id: String(props.datainformasiintelejen.upt_id),
  tanggal_input: props.datainformasiintelejen.tanggal_input,
  tanggal_pelaksanaan: props.datainformasiintelejen.tanggal_pelaksanaan || '',
  waktu_pelaksanaan: props.datainformasiintelejen.waktu_pelaksanaan || '',
  narasumber: props.datainformasiintelejen.narasumber || '',
  data_yang_masuk: props.datainformasiintelejen.data_yang_masuk || '',
  data_dukung: props.datainformasiintelejen.data_dukung || '',
  potensi_gangguan: props.datainformasiintelejen.potensi_gangguan || '',
  rekomendasi_antisipasi: props.datainformasiintelejen.rekomendasi_antisipasi || '',
  tindak_lanjut_rekomendasi: props.datainformasiintelejen.tindak_lanjut_rekomendasi || '',
})

function submit() {
  form.post(`/admin/data-informasi-intelejens/${props.datainformasiintelejen.id}`)
}
</script>

<template>
  <Head title="Input Informasi Intelijen" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-primary">Input Data Informasi Intelijen</h1>
            <p class="text-sm text-muted-foreground">Pencatatan data intelijen, potensi gangguan, dan rekomendasi.</p>
        </div>
        <Link href="/admin/data-informasi-intelejens" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Tanggal Laporan (Input)</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="rounded-xl border border-blue-200 bg-blue-50/30 p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-blue-800 border-b border-blue-200 pb-2">DATA MASUK & NARASUMBER</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Waktu Diterima (Tanggal)</label><input v-model="form.tanggal_pelaksanaan" type="date" class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Waktu Diterima (Jam)</label><input v-model="form.waktu_pelaksanaan" type="time" class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-primary">Narasumber (Internal/Eksternal)</label>
                    <input v-model="form.narasumber" type="text" placeholder="Contoh: Internal - WBP Blok A / Eksternal - Warga..." class="w-full h-9 border rounded px-3 text-sm bg-white" />
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground">Data yang Masuk</label>
                    <textarea v-model="form.data_yang_masuk" rows="4" placeholder="Uraikan informasi yang diterima..." class="w-full border rounded-md px-3 py-2 text-sm bg-white"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground">Data Dukung yang Disampaikan</label>
                    <textarea v-model="form.data_dukung" rows="3" placeholder="Bukti, foto, atau keterangan tambahan..." class="w-full border rounded-md px-3 py-2 text-sm bg-white"></textarea>
                </div>
            </div>

            <div class="rounded-xl border border-orange-200 bg-orange-50/30 p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-orange-800 border-b border-orange-200 pb-2">ANALISIS & TINDAK LANJUT</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-red-600">Potensi Gangguan Kamtib</label>
                    <textarea v-model="form.potensi_gangguan" rows="4" placeholder="Apa potensi bahaya dari informasi ini?..." class="w-full border border-red-200 rounded-md px-3 py-2 text-sm bg-white focus:ring-red-500"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-emerald-600">Rekomendasi Antisipasi</label>
                    <textarea v-model="form.rekomendasi_antisipasi" rows="3" placeholder="Saran tindakan pencegahan..." class="w-full border border-emerald-200 rounded-md px-3 py-2 text-sm bg-white focus:ring-emerald-500"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-blue-600">Tindak Lanjut Rekomendasi</label>
                    <textarea v-model="form.tindak_lanjut_rekomendasi" rows="3" placeholder="Aksi nyata yang sudah/akan dilakukan..." class="w-full border border-blue-200 rounded-md px-3 py-2 text-sm bg-white focus:ring-blue-500"></textarea>
                </div>
            </div>
        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow-lg hover:opacity-90">Simpan Laporan Intelijen</button></div>
      </form>
    </div>
  </AppLayout>
</template>