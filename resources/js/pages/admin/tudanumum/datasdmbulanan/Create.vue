<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
import { onMounted } from 'vue'

const props = defineProps<{ 
    upts: any[], jenis_pendidikans: any[], jenis_usulans: any[], 
    jenis_strukturals: any[], jenis_golongans: any[], 
    jenis_bebans: any[], jenis_staffs: any[] 
}>()

const form = useForm({
  upt_id: props.upts?.[0]?.id ? String(props.upts[0].id) : '',
  tanggal_input: new Date().toISOString().split('T')[0],
  
  // 6 JSON Objects
  jumlah_pegawai_bypendidikan: {} as Record<number, number>,
  jumlah_pegawai_byusulan: {} as Record<number, number>,
  jumlah_pejabat_struktural: {} as Record<number, number>,
  jumlah_pegawai_bygolongan: {} as Record<number, number>,
  jumlah_pegawai_bybeban: {} as Record<number, number>,
  jumlah_petugas_penjagaan: {} as Record<number, number>,
  
  // Integers
  jumlah_pegawai_laki: 0,
  jumlah_pegawai_perempuan: 0,
  jumlah_jf: 0,
  jumlah_staff_pembinaan: 0,
  jumlah_staff_pengamanan: 0,
  jumlah_staff_tudanumum: 0,
  jumlah_staff_bimker: 0,
  jumlah_pegawai_diktek: 0,
  jumlah_pegawai_dikman: 0,
  jumlah_pegawai_hukdis: 0,
})

// Isi default 0 untuk semua JSON array
onMounted(() => {
    props.jenis_pendidikans?.forEach(i => form.jumlah_pegawai_bypendidikan[i.id] = 0)
    props.jenis_usulans?.forEach(i => form.jumlah_pegawai_byusulan[i.id] = 0)
    props.jenis_strukturals?.forEach(i => form.jumlah_pejabat_struktural[i.id] = 0)
    props.jenis_golongans?.forEach(i => form.jumlah_pegawai_bygolongan[i.id] = 0)
    props.jenis_bebans?.forEach(i => form.jumlah_pegawai_bybeban[i.id] = 0)
    props.jenis_staffs?.forEach(i => form.jumlah_petugas_penjagaan[i.id] = 0)
})

function submit() {
  form.post('/admin/data-sdm-bulanans')
}
</script>

<template>
  <Head title="Input SDM Bulanan" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-primary">Input Data SDM Bulanan</h1>
            <p class="text-sm text-muted-foreground">Rincian pendidikan, pangkat, beban kerja, dan formasi staff.</p>
        </div>
        <Link href="/admin/data-sdm-bulanans" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Bulan Laporan (Pilih Tanggal)</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <h2 class="text-lg font-bold border-b pb-2 mt-4 text-blue-800">A. RINCIAN BERDASARKAN KATEGORI</h2>
        <div class="grid md:grid-cols-3 gap-6">
            
            <div class="rounded-xl border bg-blue-50/30 p-4 shadow-sm">
                <h3 class="text-sm font-bold text-blue-700 mb-3">1. Berdasarkan Pendidikan</h3>
                <div v-for="item in jenis_pendidikans" :key="item.id" class="flex justify-between items-center mb-2 bg-white p-2 rounded border">
                    <label class="text-xs font-semibold">{{ item.nama_pendidikan }}</label>
                    <input v-model.number="form.jumlah_pegawai_bypendidikan[item.id]" type="number" min="0" class="w-16 h-7 text-right text-xs border rounded" />
                </div>
            </div>

            <div class="rounded-xl border bg-indigo-50/30 p-4 shadow-sm">
                <h3 class="text-sm font-bold text-indigo-700 mb-3">4. Berdasarkan Golongan</h3>
                <div v-for="item in jenis_golongans" :key="item.id" class="flex justify-between items-center mb-2 bg-white p-2 rounded border">
                    <label class="text-xs font-semibold">{{ item.nama_golongan }}</label>
                    <input v-model.number="form.jumlah_pegawai_bygolongan[item.id]" type="number" min="0" class="w-16 h-7 text-right text-xs border rounded" />
                </div>
            </div>

            <div class="rounded-xl border bg-orange-50/30 p-4 shadow-sm">
                <h3 class="text-sm font-bold text-orange-700 mb-3">3. Pejabat Struktural</h3>
                <div v-for="item in jenis_strukturals" :key="item.id" class="flex justify-between items-center mb-2 bg-white p-2 rounded border">
                    <label class="text-xs font-semibold">{{ item.nama_struktural }}</label>
                    <input v-model.number="form.jumlah_pejabat_struktural[item.id]" type="number" min="0" class="w-16 h-7 text-right text-xs border rounded" />
                </div>
            </div>

            <div class="rounded-xl border bg-emerald-50/30 p-4 shadow-sm">
                <h3 class="text-sm font-bold text-emerald-700 mb-3">2. Jenis Usulan Pegawai</h3>
                <div v-for="item in jenis_usulans" :key="item.id" class="flex justify-between items-center mb-2 bg-white p-2 rounded border">
                    <label class="text-xs font-semibold">{{ item.nama_usulan }}</label>
                    <input v-model.number="form.jumlah_pegawai_byusulan[item.id]" type="number" min="0" class="w-16 h-7 text-right text-xs border rounded" />
                </div>
            </div>

            <div class="rounded-xl border bg-purple-50/30 p-4 shadow-sm">
                <h3 class="text-sm font-bold text-purple-700 mb-3">6. Analisa Beban Kerja</h3>
                <div v-for="item in jenis_bebans" :key="item.id" class="flex justify-between items-center mb-2 bg-white p-2 rounded border">
                    <label class="text-xs font-semibold">{{ item.nama_beban }}</label>
                    <input v-model.number="form.jumlah_pegawai_bybeban[item.id]" type="number" min="0" class="w-16 h-7 text-right text-xs border rounded" />
                </div>
            </div>

            <div class="rounded-xl border bg-slate-50 p-4 shadow-sm">
                <h3 class="text-sm font-bold text-slate-700 mb-3">8. Petugas Penjagaan</h3>
                <div v-for="item in jenis_staffs" :key="item.id" class="flex justify-between items-center mb-2 bg-white p-2 rounded border">
                    <label class="text-xs font-semibold">{{ item.nama_staff }}</label>
                    <input v-model.number="form.jumlah_petugas_penjagaan[item.id]" type="number" min="0" class="w-16 h-7 text-right text-xs border rounded" />
                </div>
            </div>
        </div>

        <h2 class="text-lg font-bold border-b pb-2 mt-8 text-emerald-800">B. FORMASI STAFF & LAINNYA</h2>
        <div class="grid md:grid-cols-4 gap-4">
            
            <div class="rounded-xl border bg-card p-4 shadow-sm space-y-3">
                <p class="text-xs font-bold text-muted-foreground border-b pb-1">5. Jenis Kelamin</p>
                <div class="space-y-1"><label class="text-[10px] uppercase font-bold">Laki-laki</label><input v-model.number="form.jumlah_pegawai_laki" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm" /></div>
                <div class="space-y-1"><label class="text-[10px] uppercase font-bold">Perempuan</label><input v-model.number="form.jumlah_pegawai_perempuan" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm" /></div>
            </div>

            <div class="rounded-xl border bg-card p-4 shadow-sm space-y-3 col-span-2">
                <p class="text-xs font-bold text-muted-foreground border-b pb-1">7 s/d 12. Jabatan & Bidang</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1"><label class="text-[10px] uppercase font-bold">7. Jabatan Fungsional</label><input v-model.number="form.jumlah_jf" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm" /></div>
                    <div class="space-y-1"><label class="text-[10px] uppercase font-bold">9. Staf Pembinaan</label><input v-model.number="form.jumlah_staff_pembinaan" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm" /></div>
                    <div class="space-y-1"><label class="text-[10px] uppercase font-bold">10. Staf KPLP/Kamtib</label><input v-model.number="form.jumlah_staff_pengamanan" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm" /></div>
                    <div class="space-y-1"><label class="text-[10px] uppercase font-bold">11. Staf TU & Umum</label><input v-model.number="form.jumlah_staff_tudanumum" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm" /></div>
                    <div class="space-y-1 col-span-2"><label class="text-[10px] uppercase font-bold">12. Staf Bimker</label><input v-model.number="form.jumlah_staff_bimker" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm" /></div>
                </div>
            </div>

            <div class="rounded-xl border bg-red-50/30 border-red-100 p-4 shadow-sm space-y-3">
                <p class="text-xs font-bold text-red-700 border-b border-red-200 pb-1">13 s/d 15. Diklat & Disiplin</p>
                <div class="space-y-1"><label class="text-[10px] uppercase font-bold">Diklat Teknis</label><input v-model.number="form.jumlah_pegawai_diktek" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                <div class="space-y-1"><label class="text-[10px] uppercase font-bold">Diklat Manajerial</label><input v-model.number="form.jumlah_pegawai_dikman" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                <div class="space-y-1"><label class="text-[10px] uppercase font-bold text-red-600">Hukuman Disiplin</label><input v-model.number="form.jumlah_pegawai_hukdis" type="number" min="0" class="w-full h-8 border border-red-200 rounded px-2 text-sm bg-red-50 font-bold text-red-700" /></div>
            </div>

        </div>

        <div class="text-right pt-4"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow-lg hover:opacity-90">Simpan Seluruh Data SDM</button></div>
      </form>
    </div>
  </AppLayout>
</template>