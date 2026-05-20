<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
import { watch } from 'vue'

const props = defineProps<{ upts: any[] }>()

const form = useForm({
  upt_id: props.upts?.[0]?.id ? String(props.upts[0].id) : '',
  tanggal_input: new Date().toISOString().split('T')[0],
  jenis_gangguan: '',
  uraian_singkat: '',
  tanggal_kejadian: '',
  waktu_kejadian: '',
  
  jumlah_korban: 0,
  detail_korban: [] as string[],
  
  jumlah_barbuk: 0,
  detail_barbuk: [] as string[],
  
  area_dirusak: '',
  pengamanan_tkp: '',
  upaya_dilakukan: '',
  permohonan_bantuan: '',
  dokumentasi: null as File | null,
})

// SIHIR 1: Looping Otomatis Korban
watch(() => form.jumlah_korban, (newVal) => {
    const count = Number(newVal) || 0;
    if (form.detail_korban.length < count) {
        while (form.detail_korban.length < count) form.detail_korban.push('');
    } else if (form.detail_korban.length > count) {
        form.detail_korban.splice(count);
    }
});

// SIHIR 2: Looping Otomatis Barbuk (Barang Bukti)
watch(() => form.jumlah_barbuk, (newVal) => {
    const count = Number(newVal) || 0;
    if (form.detail_barbuk.length < count) {
        while (form.detail_barbuk.length < count) form.detail_barbuk.push('');
    } else if (form.detail_barbuk.length > count) {
        form.detail_barbuk.splice(count);
    }
});

function submit() {
  form.post('/admin/data-gangguan-kamtibs')
}
</script>

<template>
  <Head title="Input Gangguan Kamtib" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-red-600">Input Gangguan KAMTIB</h1>
            <p class="text-sm text-muted-foreground">Laporan insiden, korban, barang bukti, dan penanganan.</p>
        </div>
        <Link href="/admin/data-gangguan-kamtibs" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Tanggal Input (Pelaporan)</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="rounded-xl border border-red-200 bg-red-50/20 p-6 shadow-sm">
            <h3 class="font-bold text-red-800 border-b border-red-200 pb-2 mb-4">WAKTU & JENIS KEJADIAN</h3>
            <div class="grid md:grid-cols-3 gap-6 mb-4">
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Tgl Kejadian</label><input v-model="form.tanggal_kejadian" type="date" required class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Waktu Kejadian</label><input v-model="form.waktu_kejadian" type="time" required class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-primary">Jenis Gangguan Kamtib</label><input v-model="form.jenis_gangguan" type="text" placeholder="Contoh: Perkelahian, Pelarian..." required class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-muted-foreground">Uraian Singkat Kejadian</label>
                <textarea v-model="form.uraian_singkat" rows="3" required placeholder="Ceritakan kronologis secara singkat..." class="w-full border rounded-md px-3 py-2 text-sm bg-white"></textarea>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-orange-600 border-b pb-2">Data Korban</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground">Jumlah Korban</label>
                    <input v-model.number="form.jumlah_korban" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm font-bold bg-orange-50 text-orange-700" />
                </div>
                <div v-if="form.jumlah_korban > 0" class="p-3 bg-muted/30 rounded border space-y-2 max-h-[300px] overflow-y-auto">
                    <p class="text-xs font-bold text-muted-foreground mb-2">Detail Identitas / Kondisi Korban:</p>
                    <input v-for="(k, idx) in form.detail_korban" :key="'k-'+idx" v-model="form.detail_korban[idx]" type="text" :placeholder="`Detail Korban ${idx + 1}...`" required class="w-full h-8 border rounded px-2 text-sm bg-white" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-emerald-600 border-b pb-2">Barang Bukti (Barbuk)</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground">Jumlah Item Barang Bukti</label>
                    <input v-model.number="form.jumlah_barbuk" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm font-bold bg-emerald-50 text-emerald-700" />
                </div>
                <div v-if="form.jumlah_barbuk > 0" class="p-3 bg-muted/30 rounded border space-y-2 max-h-[300px] overflow-y-auto">
                    <p class="text-xs font-bold text-muted-foreground mb-2">Rincian Barang Bukti:</p>
                    <input v-for="(b, idx) in form.detail_barbuk" :key="'b-'+idx" v-model="form.detail_barbuk[idx]" type="text" :placeholder="`Barang Bukti ${idx + 1}...`" required class="w-full h-8 border rounded px-2 text-sm bg-white" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Area yang Dirusak</label><input v-model="form.area_dirusak" type="text" placeholder="Contoh: Pintu Blok B, Jendela..." class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
            <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Pengamanan TKP</label><input v-model="form.pengamanan_tkp" type="text" placeholder="Tindakan awal di lokasi..." class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
            <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Upaya yang Telah Dilakukan</label><textarea v-model="form.upaya_dilakukan" rows="2" class="w-full border rounded-md px-3 py-2 text-sm bg-background"></textarea></div>
            <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Permohonan Bantuan</label><textarea v-model="form.permohonan_bantuan" rows="2" placeholder="TNI/POLRI/Damkar..." class="w-full border rounded-md px-3 py-2 text-sm bg-background"></textarea></div>
        </div>

        <div class="rounded-xl border bg-blue-50 p-6 shadow-sm border-blue-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-blue-800">Upload Laporan / BAP (PDF)</h3>
                <p class="text-xs text-blue-600 mt-1">Sertakan file Berita Acara Pemeriksaan atau Laporan Kejadian.</p>
            </div>
            <input type="file" accept="application/pdf" @input="form.dokumentasi = $event.target.files[0]" required class="text-sm file:mr-3 file:rounded file:border-0 file:bg-blue-600 file:text-white file:px-4 file:py-2 hover:file:bg-blue-700 cursor-pointer" />
        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-red-600 text-white px-8 py-3 rounded-md font-bold shadow-lg hover:bg-red-700 transition-colors">Simpan Laporan Kejadian</button></div>
      </form>
    </div>
  </AppLayout>
</template>