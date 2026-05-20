<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
import { onMounted } from 'vue'

const props = defineProps<{ datapenghuniriil: any, upts: any[], jenis_pengeluarans: any[] }>()

const form = useForm({
  _method: 'put',
  upt_id: String(props.datapenghuniriil.upt_id),
  tanggal_input: props.datapenghuniriil.tanggal_input,
  
  nama_regu: props.datapenghuniriil.nama_regu, jumlah_regu: props.datapenghuniriil.jumlah_regu, jumlah_hadir: props.datapenghuniriil.jumlah_hadir, jumlah_tidak_hadir: props.datapenghuniriil.jumlah_tidak_hadir,
  keterangan: props.datapenghuniriil.keterangan || '', perwira_piket: props.datapenghuniriil.perwira_piket || '', jumlah_bantuan_piket: props.datapenghuniriil.jumlah_bantuan_piket,
  pengawalan_rs: props.datapenghuniriil.pengawalan_rs || '', patroli_sambang: props.datapenghuniriil.patroli_sambang || '', waktu_patroli_sambang: props.datapenghuniriil.waktu_patroli_sambang || '',

  jumlah_kapasitas: props.datapenghuniriil.jumlah_kapasitas,
  jumlah_napi_laki: props.datapenghuniriil.jumlah_napi_laki, jumlah_napi_perempuan: props.datapenghuniriil.jumlah_napi_perempuan, jumlah_napi_anak: props.datapenghuniriil.jumlah_napi_anak,
  jumlah_tahanan_laki: props.datapenghuniriil.jumlah_tahanan_laki, jumlah_tahanan_perempuan: props.datapenghuniriil.jumlah_tahanan_perempuan, jumlah_tahanan_anak: props.datapenghuniriil.jumlah_tahanan_anak,
  total_isi: props.datapenghuniriil.total_isi, overcrowded: props.datapenghuniriil.overcrowded,

  jumlah_pengeluaran: props.datapenghuniriil.jumlah_pengeluaran || {}
})

onMounted(() => {
    props.jenis_pengeluarans?.forEach(item => {
        if (form.jumlah_pengeluaran[item.id] === undefined) form.jumlah_pengeluaran[item.id] = 0;
    })
})

function hitungHunian() {
    const total = 
        (form.jumlah_napi_laki || 0) + (form.jumlah_napi_perempuan || 0) + (form.jumlah_napi_anak || 0) +
        (form.jumlah_tahanan_laki || 0) + (form.jumlah_tahanan_perempuan || 0) + (form.jumlah_tahanan_anak || 0);
    form.total_isi = total;
    if ((form.jumlah_kapasitas || 0) > 0) {
        const over = ((total - form.jumlah_kapasitas) / form.jumlah_kapasitas) * 100;
        form.overcrowded = Number(over.toFixed(2));
    } else {
        form.overcrowded = 0;
    }
}

function submit() {
  form.post(`/admin/data-penghuni-riils/${props.datapenghuniriil.id}`)
}
</script>

<template>
  <Head title="Input Penghuni Riil" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-primary">Input Data Jumlah Penghuni Riil</h1>
            <p class="text-sm text-muted-foreground">Laporan data pada papan lalu lintas portir.</p>
        </div>
        <Link href="/admin/data-penghuni-riils" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-4 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Tanggal Input</label><input v-model="form.tanggal_input" type="date" class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            
            <div class="rounded-xl border border-blue-200 bg-blue-50/30 p-5 shadow-sm">
                <h3 class="font-bold text-blue-800 text-lg border-b border-blue-200 pb-2 mb-4">KEKUATAN PENGAMANAN</h3>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1"><label class="text-xs font-bold text-muted-foreground">Nama Regu</label><input v-model="form.nama_regu" type="text" class="w-full h-8 border rounded px-2 text-sm bg-white" required /></div>
                        <div class="space-y-1"><label class="text-xs font-bold text-muted-foreground">Jml Regu</label><input v-model.number="form.jumlah_regu" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1"><label class="text-xs font-bold text-emerald-600">Hadir</label><input v-model.number="form.jumlah_hadir" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                        <div class="space-y-1"><label class="text-xs font-bold text-red-600">Tidak Hadir</label><input v-model.number="form.jumlah_tidak_hadir" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                    </div>
                    <div class="space-y-1"><label class="text-xs font-bold text-muted-foreground">Keterangan Tidak Hadir</label><input v-model="form.keterangan" type="text" placeholder="Isi jika ada yg tidak hadir..." class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                    
                    <div class="grid grid-cols-2 gap-2 mt-4 border-t pt-4">
                        <div class="space-y-1"><label class="text-xs font-bold text-muted-foreground">Perwira Piket</label><input v-model="form.perwira_piket" type="text" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                        <div class="space-y-1"><label class="text-xs font-bold text-muted-foreground">Bantuan Piket</label><input v-model.number="form.jumlah_bantuan_piket" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                    </div>
                    
                    <div class="space-y-1"><label class="text-xs font-bold text-muted-foreground">Pengawalan RS/Asimilasi</label><input v-model="form.pengawalan_rs" type="text" placeholder="Nama..." class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                    
                    <div class="grid grid-cols-2 gap-2 border-t pt-2">
                        <div class="space-y-1"><label class="text-xs font-bold text-muted-foreground">Patroli Sambang</label>
                            <select v-model="form.patroli_sambang" class="w-full h-8 border rounded px-2 text-sm bg-white">
                                <option value="">-- Pilih --</option>
                                <option value="TNI">TNI</option>
                                <option value="POLRI">POLRI</option>
                                <option value="TNI/POLRI">TNI/POLRI</option>
                            </select>
                        </div>
                        <div class="space-y-1"><label class="text-xs font-bold text-muted-foreground">Waktu Sambang</label><input v-model="form.waktu_patroli_sambang" type="time" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-emerald-200 bg-emerald-50/30 p-5 shadow-sm">
                <h3 class="font-bold text-emerald-800 text-lg border-b border-emerald-200 pb-2 mb-4">DATA HUNIAN</h3>
                <div class="space-y-4">
                    <div class="space-y-1 bg-emerald-100/50 p-2 rounded border border-emerald-200"><label class="text-xs font-bold text-emerald-800">Kapasitas UPT</label><input v-model.number="form.jumlah_kapasitas" @input="hitungHunian" type="number" min="0" class="w-full h-9 border rounded px-2 text-sm font-bold bg-white" /></div>
                    
                    <div class="border rounded-md p-3 bg-white">
                        <p class="text-xs font-bold mb-2">Jumlah Napi</p>
                        <div class="grid grid-cols-3 gap-2">
                            <div><label class="text-[10px] text-muted-foreground">Laki-laki</label><input v-model.number="form.jumlah_napi_laki" @input="hitungHunian" type="number" min="0" class="w-full h-7 border rounded px-1 text-xs" /></div>
                            <div><label class="text-[10px] text-muted-foreground">Perempuan</label><input v-model.number="form.jumlah_napi_perempuan" @input="hitungHunian" type="number" min="0" class="w-full h-7 border rounded px-1 text-xs" /></div>
                            <div><label class="text-[10px] text-muted-foreground">Anak</label><input v-model.number="form.jumlah_napi_anak" @input="hitungHunian" type="number" min="0" class="w-full h-7 border rounded px-1 text-xs" /></div>
                        </div>
                    </div>

                    <div class="border rounded-md p-3 bg-white">
                        <p class="text-xs font-bold mb-2">Jumlah Tahanan</p>
                        <div class="grid grid-cols-3 gap-2">
                            <div><label class="text-[10px] text-muted-foreground">Laki-laki</label><input v-model.number="form.jumlah_tahanan_laki" @input="hitungHunian" type="number" min="0" class="w-full h-7 border rounded px-1 text-xs" /></div>
                            <div><label class="text-[10px] text-muted-foreground">Perempuan</label><input v-model.number="form.jumlah_tahanan_perempuan" @input="hitungHunian" type="number" min="0" class="w-full h-7 border rounded px-1 text-xs" /></div>
                            <div><label class="text-[10px] text-muted-foreground">Anak</label><input v-model.number="form.jumlah_tahanan_anak" @input="hitungHunian" type="number" min="0" class="w-full h-7 border rounded px-1 text-xs" /></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div class="bg-card border rounded p-2 text-center shadow-sm">
                            <p class="text-[10px] uppercase font-bold text-muted-foreground">Total Isi</p>
                            <p class="text-xl font-black text-emerald-600">{{ form.total_isi }}</p>
                        </div>
                        <div class="bg-card border rounded p-2 text-center shadow-sm" :class="{'border-red-300 bg-red-50': form.overcrowded > 0}">
                            <p class="text-[10px] uppercase font-bold text-muted-foreground">Overcrowded</p>
                            <p class="text-xl font-black" :class="form.overcrowded > 0 ? 'text-red-600' : 'text-emerald-600'">{{ form.overcrowded }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-orange-200 bg-orange-50/30 p-5 shadow-sm">
                <h3 class="font-bold text-orange-800 text-lg border-b border-orange-200 pb-2 mb-4">PENGELUARAN TAHANAN/NAPI</h3>
                <div class="space-y-3">
                    <div v-for="item in jenis_pengeluarans" :key="item.id" class="flex justify-between items-center bg-white p-2 rounded-md border shadow-sm">
                        <label class="text-xs font-semibold">{{ item.nama_pengeluaran }}</label>
                        <input v-model.number="form.jumlah_pengeluaran[item.id]" type="number" min="0" class="w-16 h-7 rounded border border-input px-2 text-xs text-right bg-background focus:ring-orange-500" />
                    </div>
                </div>
            </div>

        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-10 py-3 rounded-md font-bold shadow-lg hover:opacity-90">Simpan Perubahan Papan Portir</button></div>
      </form>
    </div>
  </AppLayout>
</template>