<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ upts: any[] }>()

const form = useForm({
  upt_id: props.upts?.[0]?.id ? String(props.upts[0].id) : '',
  tanggal_input: new Date().toISOString().split('T')[0],
  jumlah_senjata_api_baik: 0,
  jumlah_senjata_api_rusak: 0,
  jumlah_amunisi: 0,
  jumlah_xray: 0,
  jumlah_body_scanner: 0,
  jumlah_phh: 0,
  jumlah_borgol: 0,
  jumlah_metal_detector: 0,
  jumlah_cctv: 0,
  jumlah_apar: 0,
  jumlah_lonceng: 0,
  jumlah_ht: 0,
})

function submit() {
  form.post('/admin/data-sarpras-keamanans')
}
</script>

<template>
  <Head title="Input Sarpras Keamanan" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-primary">Input Sarpras Keamanan</h1>
            <p class="text-sm text-muted-foreground">Laporan inventaris Sarana dan Prasarana Keamanan UPT.</p>
        </div>
        <Link href="/admin/data-sarpras-keamanans" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Tanggal Input (Pelaporan)</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            
            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-red-700 border-b pb-2">Persenjataan & Amunisi</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground">1. Jumlah Senjata Api</label>
                    <div class="flex gap-2">
                        <div class="w-1/2 flex items-center gap-2"><span class="text-xs text-emerald-600 font-bold">Baik:</span><input v-model.number="form.jumlah_senjata_api_baik" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-emerald-50 text-emerald-700 font-bold text-center" /></div>
                        <div class="w-1/2 flex items-center gap-2"><span class="text-xs text-red-600 font-bold">Rusak:</span><input v-model.number="form.jumlah_senjata_api_rusak" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-red-50 text-red-700 font-bold text-center" /></div>
                    </div>
                </div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">2. Amunisi Senjata Api</label><input v-model.number="form.jumlah_amunisi" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background text-right" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">5. Alat PHH</label><input v-model.number="form.jumlah_phh" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background text-right" /></div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-blue-700 border-b pb-2">Deteksi & Pemantauan</h3>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">3. Mesin X-Ray</label><input v-model.number="form.jumlah_xray" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background text-right" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">4. Body Scanner</label><input v-model.number="form.jumlah_body_scanner" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background text-right" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">7. Metal Detector</label><input v-model.number="form.jumlah_metal_detector" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background text-right" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">8. CCTV Aktif</label><input v-model.number="form.jumlah_cctv" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background text-right" /></div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-orange-700 border-b pb-2">Perlengkapan Dasar</h3>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">6. Borgol</label><input v-model.number="form.jumlah_borgol" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background text-right" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">9. Alat Pemadam Ringan (APAR)</label><input v-model.number="form.jumlah_apar" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background text-right" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">10. Lonceng</label><input v-model.number="form.jumlah_lonceng" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background text-right" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">11. Handy Talky (HT)</label><input v-model.number="form.jumlah_ht" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm bg-background text-right" /></div>
            </div>

        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow-lg hover:opacity-90">Simpan Laporan Inventaris</button></div>
      </form>
    </div>
  </AppLayout>
</template>