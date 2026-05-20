<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ upts: any[] }>()

const form = useForm({
  upt_id: props.upts?.[0]?.id ? String(props.upts[0].id) : '',
  tanggal_input: new Date().toISOString().split('T')[0],
  jumlah_pegawai_keseluruhan: 0,
  
  // JSON Kehadiran
  kehadiran_pegawai: {
      hadir: 0,
      cuti: 0,
      sakit: 0,
      ijin: 0
  },
  
  pelanggaran_sdm: '',
})

// Sihir Auto Hitung Hadir
function hitungHadir() {
    const total = Number(form.jumlah_pegawai_keseluruhan) || 0;
    const cuti = Number(form.kehadiran_pegawai.cuti) || 0;
    const sakit = Number(form.kehadiran_pegawai.sakit) || 0;
    const ijin = Number(form.kehadiran_pegawai.ijin) || 0;
    
    const sisaHadir = total - (cuti + sakit + ijin);
    // Pastikan tidak minus jika salah input
    form.kehadiran_pegawai.hadir = sisaHadir >= 0 ? sisaHadir : 0;
}

function submit() {
  form.post('/admin/data-harian-sdms')
}
</script>

<template>
  <Head title="Input Harian SDM" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-primary">Input Data Harian SDM</h1>
            <p class="text-sm text-muted-foreground">Laporan kehadiran pegawai dan pelanggaran SDM (TU & Umum).</p>
        </div>
        <Link href="/admin/data-harian-sdms" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Tanggal Laporan</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            
            <div class="rounded-xl border border-blue-200 bg-blue-50/30 p-6 shadow-sm space-y-5">
                <h3 class="font-bold text-blue-800 text-lg border-b border-blue-200 pb-2">DATA KEHADIRAN PEGAWAI</h3>
                
                <div class="space-y-2">
                    <label class="text-xs font-bold text-primary uppercase">Jumlah Pegawai Keseluruhan</label>
                    <input v-model.number="form.jumlah_pegawai_keseluruhan" @input="hitungHadir" type="number" min="0" class="w-full h-12 border border-blue-300 rounded-md px-4 text-xl font-black text-blue-900 bg-white focus:ring-blue-500" />
                </div>

                <div class="grid grid-cols-3 gap-4 bg-white p-4 rounded-lg border shadow-sm">
                    <div class="space-y-1"><label class="text-[10px] font-bold text-muted-foreground uppercase">Cuti</label><input v-model.number="form.kehadiran_pegawai.cuti" @input="hitungHadir" type="number" min="0" class="w-full h-9 border rounded px-2 text-sm bg-orange-50 text-orange-700 font-bold text-center" /></div>
                    <div class="space-y-1"><label class="text-[10px] font-bold text-muted-foreground uppercase">Sakit</label><input v-model.number="form.kehadiran_pegawai.sakit" @input="hitungHadir" type="number" min="0" class="w-full h-9 border rounded px-2 text-sm bg-yellow-50 text-yellow-700 font-bold text-center" /></div>
                    <div class="space-y-1"><label class="text-[10px] font-bold text-muted-foreground uppercase">Ijin</label><input v-model.number="form.kehadiran_pegawai.ijin" @input="hitungHadir" type="number" min="0" class="w-full h-9 border rounded px-2 text-sm bg-slate-100 text-slate-700 font-bold text-center" /></div>
                </div>

                <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-lg flex items-center justify-between">
                    <div>
                        <p class="font-bold text-emerald-800 uppercase">Total Hadir Bertugas</p>
                        <p class="text-[10px] text-emerald-600">Dihitung otomatis oleh sistem</p>
                    </div>
                    <div class="text-3xl font-black text-emerald-600">{{ form.kehadiran_pegawai.hadir }}</div>
                </div>
            </div>

            <div class="rounded-xl border border-red-200 bg-red-50/30 p-6 shadow-sm space-y-4">
                <h3 class="font-bold text-red-800 text-lg border-b border-red-200 pb-2">PELANGGARAN SDM</h3>
                <div class="space-y-2 h-full">
                    <label class="text-xs font-bold text-muted-foreground uppercase">Catatan Pelanggaran Pegawai</label>
                    <textarea v-model="form.pelanggaran_sdm" rows="10" placeholder="Kosongkan atau ketik 'Nihil' jika tidak ada pelanggaran SDM pada hari ini..." class="w-full border rounded-md px-4 py-3 text-sm bg-white focus:ring-red-500"></textarea>
                </div>
            </div>

        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow-lg hover:opacity-90">Simpan Laporan SDM</button></div>
      </form>
    </div>
  </AppLayout>
</template>