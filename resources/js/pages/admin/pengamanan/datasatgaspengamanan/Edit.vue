<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ datasatgaspengamanan: any, upts: any[] }>()

const form = useForm({
  _method: 'put',
  upt_id: String(props.datasatgaspengamanan.upt_id),
  tanggal_input: props.datasatgaspengamanan.tanggal_input,
  
  tanggal_pelaksanaan_penggeledahan: props.datasatgaspengamanan.tanggal_pelaksanaan_penggeledahan || '',
  waktu_pelaksanaan_penggeledahan: props.datasatgaspengamanan.waktu_pelaksanaan_penggeledahan || '',
  lokasi_kamar_penggeledahan: props.datasatgaspengamanan.lokasi_kamar_penggeledahan || '',
  jumlah_anggota_penggeledahan: props.datasatgaspengamanan.jumlah_anggota_penggeledahan,
  hasil_penggeledahan: props.datasatgaspengamanan.hasil_penggeledahan || '',
  tindak_lanjut_penggeledahan: props.datasatgaspengamanan.tindak_lanjut_penggeledahan || '',
  
  tanggal_pelaksanaan_narkoba: props.datasatgaspengamanan.tanggal_pelaksanaan_narkoba || '',
  waktu_pelaksanaan_narkoba: props.datasatgaspengamanan.waktu_pelaksanaan_narkoba || '',
  jumlah_yang_dites_narkoba: props.datasatgaspengamanan.jumlah_yang_dites_narkoba,
  hasil_tes_narkoba: props.datasatgaspengamanan.hasil_tes_narkoba || '',
  tindak_lanjut_hasil_tes: props.datasatgaspengamanan.tindak_lanjut_hasil_tes || '',
})

function submit() {
  form.post(`/admin/data-satgas-pengamanans/${props.datasatgaspengamanan.id}`)
}
</script>

<template>
  <Head title="Input Satgas Pengamanan" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-primary">Input Laporan Satgas</h1>
            <p class="text-sm text-muted-foreground">Laporan kegiatan razia kamar dan tes narkoba.</p>
        </div>
        <Link href="/admin/data-satgas-pengamanans" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Tanggal Input (Pelaporan)</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            
            <div class="rounded-xl border border-blue-200 bg-blue-50/20 p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-blue-800 text-lg border-b border-blue-200 pb-2">KEGIATAN RAZIA / PENGGELEDAHAN</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Tgl Pelaksanaan</label><input v-model="form.tanggal_pelaksanaan_penggeledahan" type="date" class="w-full h-9 border rounded px-2 text-sm bg-white" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Waktu Pelaksanaan</label><input v-model="form.waktu_pelaksanaan_penggeledahan" type="time" class="w-full h-9 border rounded px-2 text-sm bg-white" /></div>
                </div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Lokasi / Kamar</label><input v-model="form.lokasi_kamar_penggeledahan" type="text" placeholder="Contoh: Blok B Kamar 3..." class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Jumlah Anggota</label><input v-model.number="form.jumlah_anggota_penggeledahan" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Hasil Penggeledahan</label><textarea v-model="form.hasil_penggeledahan" rows="3" placeholder="Sebutkan barang temuan..." class="w-full border rounded px-3 py-2 text-sm bg-white"></textarea></div>
                <div class="space-y-2"><label class="text-xs font-bold text-blue-600">Tindak Lanjut Hasil Razia</label><textarea v-model="form.tindak_lanjut_penggeledahan" rows="2" placeholder="Tindakan yang diambil..." class="w-full border rounded px-3 py-2 text-sm bg-white"></textarea></div>
            </div>

            <div class="rounded-xl border border-emerald-200 bg-emerald-50/20 p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-emerald-800 text-lg border-b border-emerald-200 pb-2">KEGIATAN TES NARKOBA</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Tgl Pelaksanaan</label><input v-model="form.tanggal_pelaksanaan_narkoba" type="date" class="w-full h-9 border rounded px-2 text-sm bg-white" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Waktu Pelaksanaan</label><input v-model="form.waktu_pelaksanaan_narkoba" type="time" class="w-full h-9 border rounded px-2 text-sm bg-white" /></div>
                </div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Jumlah Yang Dites</label><input v-model.number="form.jumlah_yang_dites_narkoba" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Hasil Tes</label><textarea v-model="form.hasil_tes_narkoba" rows="3" placeholder="Contoh: 10 Negatif, 2 Positif..." class="w-full border rounded px-3 py-2 text-sm bg-white"></textarea></div>
                <div class="space-y-2"><label class="text-xs font-bold text-emerald-600">Tindak Lanjut Hasil Tes</label><textarea v-model="form.tindak_lanjut_hasil_tes" rows="4" placeholder="Tindakan medis/hukuman..." class="w-full border rounded px-3 py-2 text-sm bg-white"></textarea></div>
            </div>

        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow-lg hover:opacity-90">Simpan Laporan Satgas</button></div>
      </form>
    </div>
  </AppLayout>
</template>