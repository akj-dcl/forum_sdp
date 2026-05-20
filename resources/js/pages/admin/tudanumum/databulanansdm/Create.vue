<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ upts: any[] }>()

const form = useForm({
  upt_id: props.upts?.[0]?.id ? String(props.upts[0].id) : '',
  tanggal_input: new Date().toISOString().split('T')[0],
  jumlah_pegawai: 0,
  jumlah_pejabat_struktural: 0,
  jumlah_jf: 0,
  jumlah_petugas_penjagaan: 0,
  jumlah_staff_pembinaan: 0,
  jumlah_staff: 0,
})

// SIHIR VUE: Hitung otomatis Total Pegawai
function hitungTotal() {
    form.jumlah_pegawai = 
        (Number(form.jumlah_pejabat_struktural) || 0) + 
        (Number(form.jumlah_jf) || 0) + 
        (Number(form.jumlah_petugas_penjagaan) || 0) + 
        (Number(form.jumlah_staff_pembinaan) || 0) + 
        (Number(form.jumlah_staff) || 0);
}

function submit() {
  form.post('/admin/data-bulanan-sdms')
}
</script>

<template>
  <Head title="Input Bulanan SDM" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-primary">Input Data Bulanan SDM</h1>
            <p class="text-sm text-muted-foreground">Laporan rekapitulasi jumlah pegawai dan staf (TU & Umum).</p>
        </div>
        <Link href="/admin/data-bulanan-sdms" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Bulan Laporan (Pilih Tanggal)</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50/20 p-6 shadow-sm flex flex-col md:flex-row gap-8">
            
            <div class="flex-1 space-y-4">
                <h3 class="font-bold text-blue-800 text-lg border-b border-blue-200 pb-2">RINCIAN FORMASI PEGAWAI</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">2. Pejabat Struktural</label><input v-model.number="form.jumlah_pejabat_struktural" @input="hitungTotal" type="number" min="0" class="w-full h-10 border rounded px-3 text-sm bg-white" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">3. Jabatan Fungsional (JF)</label><input v-model.number="form.jumlah_jf" @input="hitungTotal" type="number" min="0" class="w-full h-10 border rounded px-3 text-sm bg-white" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">4. Petugas Penjagaan</label><input v-model.number="form.jumlah_petugas_penjagaan" @input="hitungTotal" type="number" min="0" class="w-full h-10 border rounded px-3 text-sm bg-white" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">5. Staf Pembinaan</label><input v-model.number="form.jumlah_staff_pembinaan" @input="hitungTotal" type="number" min="0" class="w-full h-10 border rounded px-3 text-sm bg-white" /></div>
                    <div class="space-y-2 col-span-2"><label class="text-xs font-bold text-muted-foreground">6. Staf Lainnya</label><input v-model.number="form.jumlah_staff" @input="hitungTotal" type="number" min="0" class="w-full h-10 border rounded px-3 text-sm bg-white" /></div>
                </div>
            </div>

            <div class="md:w-1/3 bg-white p-6 rounded-xl border shadow-sm flex flex-col justify-center items-center text-center">
                <p class="text-sm font-bold text-primary mb-2 uppercase">1. Total Pegawai Keseluruhan</p>
                <input v-model.number="form.jumlah_pegawai" type="number" readonly class="w-full h-16 border-2 border-emerald-200 rounded-lg px-4 text-4xl font-black text-emerald-700 bg-emerald-50 text-center cursor-not-allowed focus:outline-none" />
                <p class="text-[10px] text-muted-foreground mt-3 italic">Dihitung otomatis berdasarkan rincian di samping.</p>
            </div>

        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow-lg hover:opacity-90">Simpan Laporan Bulanan</button></div>
      </form>
    </div>
  </AppLayout>
</template>