<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps<{ upts: any[], jenis_pengawalans: any[], jenis_klasifikasis: any[] }>()

const form = useForm({
  upt_id: props.upts?.[0]?.id ? String(props.upts[0].id) : '',
  tanggal_input: new Date().toISOString().split('T')[0],
  tanggal_pelaksanaan: '',
  waktu_pelaksanaan_mulai: '',
  waktu_pelaksanaan_selesai: '',
  jenis_pengawalan_id: '',
  jenis_klasifikasi_id: '',
  sarana_pengawalan: '',
  
  jumlah_wbp: 0,
  detail_wbp_dikawal: [] as string[],
  
  jumlah_petugas: 0,
  detail_petugas_pengawalan: [] as string[],
  
  // 3 File Upload
  surat_perintah: null as File | null,
  laporan_pelaksanaan_pengawalan: null as File | null,
  dokumentasi: null as File | null,
})

watch(() => form.jumlah_wbp, (newVal) => {
    const count = Number(newVal) || 0;
    if (form.detail_wbp_dikawal.length < count) {
        while (form.detail_wbp_dikawal.length < count) form.detail_wbp_dikawal.push('');
    } else if (form.detail_wbp_dikawal.length > count) {
        form.detail_wbp_dikawal.splice(count);
    }
});

watch(() => form.jumlah_petugas, (newVal) => {
    const count = Number(newVal) || 0;
    if (form.detail_petugas_pengawalan.length < count) {
        while (form.detail_petugas_pengawalan.length < count) form.detail_petugas_pengawalan.push('');
    } else if (form.detail_petugas_pengawalan.length > count) {
        form.detail_petugas_pengawalan.splice(count);
    }
});

function submit() {
  form.post('/admin/data-pengawalan-pengamanans')
}
</script>

<template>
  <Head title="Input Pengawalan Luar" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-primary">Input Data Pengawalan Luar Tembok</h1>
            <p class="text-sm text-muted-foreground">Laporan pengawalan berobat, sidang, asimilasi, dll.</p>
        </div>
        <Link href="/admin/data-pengawalan-pengamanans" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Tanggal Input (Laporan)</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50/30 p-6 shadow-sm">
            <h3 class="font-bold text-blue-800 border-b border-blue-200 pb-2 mb-4">WAKTU & JENIS PENGAWALAN</h3>
            <div class="grid md:grid-cols-3 gap-4 mb-4">
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Tgl Pelaksanaan</label><input v-model="form.tanggal_pelaksanaan" type="date" required class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Waktu Mulai</label><input v-model="form.waktu_pelaksanaan_mulai" type="time" required class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Waktu Selesai</label><input v-model="form.waktu_pelaksanaan_selesai" type="time" required class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="space-y-2"><label class="text-xs font-bold text-primary uppercase">Jenis Pengawalan</label>
                    <select v-model="form.jenis_pengawalan_id" required class="w-full h-9 border rounded px-3 text-sm bg-white">
                        <option value="">-- Pilih Jenis --</option>
                        <option v-for="jp in jenis_pengawalans" :value="String(jp.id)">{{ jp.nama_pengawalan }}</option>
                    </select>
                </div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Sarana Pengawalan</label><input v-model="form.sarana_pengawalan" type="text" placeholder="Contoh: Mobil Transpas, Motor..." required class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-orange-600 border-b pb-2">Data WBP yang Dikawal</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground">Klasifikasi Risiko WBP</label>
                    <select v-model="form.jenis_klasifikasi_id" required class="w-full h-9 border rounded px-3 text-sm bg-background">
                        <option value="">-- Pilih Risiko --</option>
                        <option v-for="jk in jenis_klasifikasis" :value="String(jk.id)">{{ jk.nama_klasifikasi }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-orange-700">Jumlah WBP</label>
                    <input v-model.number="form.jumlah_wbp" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm font-bold bg-orange-50 text-orange-800" />
                </div>
                <div v-if="form.jumlah_wbp > 0" class="p-3 bg-muted/30 rounded border space-y-2 max-h-[300px] overflow-y-auto">
                    <p class="text-xs font-bold text-muted-foreground mb-2">Nama-nama WBP:</p>
                    <input v-for="(k, idx) in form.detail_wbp_dikawal" :key="'wbp-'+idx" v-model="form.detail_wbp_dikawal[idx]" type="text" :placeholder="`Nama WBP ke-${idx + 1}`" required class="w-full h-8 border rounded px-2 text-sm bg-white" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-emerald-600 border-b pb-2">Petugas Pengawal</h3>
                <div class="space-y-2 mt-16">
                    <label class="text-xs font-bold text-emerald-700">Jumlah Petugas</label>
                    <input v-model.number="form.jumlah_petugas" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm font-bold bg-emerald-50 text-emerald-800" />
                </div>
                <div v-if="form.jumlah_petugas > 0" class="p-3 bg-muted/30 rounded border space-y-2 max-h-[300px] overflow-y-auto">
                    <p class="text-xs font-bold text-muted-foreground mb-2">Nama Petugas Pengawal:</p>
                    <input v-for="(b, idx) in form.detail_petugas_pengawalan" :key="'ptg-'+idx" v-model="form.detail_petugas_pengawalan[idx]" type="text" :placeholder="`Nama Petugas ke-${idx + 1}`" required class="w-full h-8 border rounded px-2 text-sm bg-white" />
                </div>
            </div>
        </div>

        <h3 class="font-bold text-lg pt-4 border-t">Lampiran Dokumen</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div class="rounded-xl border bg-orange-50 p-4 shadow-sm border-orange-100 flex flex-col justify-center">
                <h3 class="font-bold text-orange-800 mb-1 text-sm">📜 Surat Perintah (PDF)</h3>
                <input type="file" accept="application/pdf" @input="form.surat_perintah = $event.target.files[0]" required class="text-xs mt-2 file:mr-2 file:rounded file:border-0 file:bg-orange-600 file:text-white file:px-3 file:py-1 hover:file:bg-orange-700 cursor-pointer" />
            </div>
            
            <div class="rounded-xl border bg-blue-50 p-4 shadow-sm border-blue-100 flex flex-col justify-center">
                <h3 class="font-bold text-blue-800 mb-1 text-sm">📄 Laporan Pelaksanaan (PDF)</h3>
                <input type="file" accept="application/pdf" @input="form.laporan_pelaksanaan_pengawalan = $event.target.files[0]" required class="text-xs mt-2 file:mr-2 file:rounded file:border-0 file:bg-blue-600 file:text-white file:px-3 file:py-1 hover:file:bg-blue-700 cursor-pointer" />
            </div>
            
            <div class="rounded-xl border bg-indigo-50 p-4 shadow-sm border-indigo-100 flex flex-col justify-center">
                <h3 class="font-bold text-indigo-800 mb-1 text-sm">🖼️ Dokumentasi (Foto)</h3>
                <input type="file" accept="image/*" @input="form.dokumentasi = $event.target.files[0]" required class="text-xs mt-2 file:mr-2 file:rounded file:border-0 file:bg-indigo-600 file:text-white file:px-3 file:py-1 hover:file:bg-indigo-700 cursor-pointer" />
            </div>
        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow-lg hover:opacity-90">Simpan Data Pengawalan</button></div>
      </form>
    </div>
  </AppLayout>
</template>