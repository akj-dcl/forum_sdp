<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ datarealisasianggaran: any, upts: any[] }>()

const form = useForm({
  _method: 'put',
  upt_id: String(props.datarealisasianggaran.upt_id),
  tanggal_input: props.datarealisasianggaran.tanggal_input,
  
  belanja_pegawai_pagu: props.datarealisasianggaran.belanja_pegawai_pagu, belanja_pegawai_realisasi: props.datarealisasianggaran.belanja_pegawai_realisasi,
  belanja_barang_pagu: props.datarealisasianggaran.belanja_barang_pagu, belanja_barang_realisasi: props.datarealisasianggaran.belanja_barang_realisasi,
  belanja_modal_pagu: props.datarealisasianggaran.belanja_modal_pagu, belanja_modal_realisasi: props.datarealisasianggaran.belanja_modal_realisasi,
  belanja_listrik_pagu: props.datarealisasianggaran.belanja_listrik_pagu, belanja_listrik_realisasi: props.datarealisasianggaran.belanja_listrik_realisasi,
  belanja_telpon_pagu: props.datarealisasianggaran.belanja_telpon_pagu, belanja_telpon_realisasi: props.datarealisasianggaran.belanja_telpon_realisasi,
  belanja_internet_pagu: props.datarealisasianggaran.belanja_internet_pagu, belanja_internet_realisasi: props.datarealisasianggaran.belanja_internet_realisasi,
  belanja_pos_pagu: props.datarealisasianggaran.belanja_pos_pagu, belanja_pos_realisasi: props.datarealisasianggaran.belanja_pos_realisasi,
  
  bama_nomor_kontrak: props.datarealisasianggaran.bama_nomor_kontrak || '',
  bama_pagu_anggaran: props.datarealisasianggaran.bama_pagu_anggaran,
  bama_realisasi_anggaran: props.datarealisasianggaran.bama_realisasi_anggaran,
  bama_nama_penyedia: props.datarealisasianggaran.bama_nama_penyedia || '',
  bama_nomor_bast: props.datarealisasianggaran.bama_nomor_bast || '',
  bama_tanggal_bast: props.datarealisasianggaran.bama_tanggal_bast || '',
  bama_berita_acara: null as File | null,
})

function submit() {
  form.post(`/admin/data-realisasi-anggarans/${props.datarealisasianggaran.id}`, { forceFormData: true })
}
</script>

<template>
  <Head title="Input Realisasi Anggaran" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-primary">Input Realisasi Anggaran</h1>
            <p class="text-sm text-muted-foreground">Laporan serapan dana DIPA dan BAMA UPT.</p>
        </div>
        <Link href="/admin/data-realisasi-anggarans" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Bulan Laporan (Tgl Input)</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="rounded-xl border border-blue-200 bg-blue-50/30 p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-blue-800 border-b border-blue-200 pb-2">A. BELANJA UTAMA (Rp)</h3>
                
                <div class="space-y-1">
                    <label class="text-xs font-bold text-primary uppercase">1. Belanja Pegawai</label>
                    <div class="flex gap-2">
                        <div class="w-1/2"><span class="text-[10px] text-muted-foreground">Pagu:</span><input v-model.number="form.belanja_pegawai_pagu" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                        <div class="w-1/2"><span class="text-[10px] text-emerald-600 font-bold">Realisasi:</span><input v-model.number="form.belanja_pegawai_realisasi" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-emerald-50 text-emerald-700 font-bold" /></div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-primary uppercase">2. Belanja Barang</label>
                    <div class="flex gap-2">
                        <div class="w-1/2"><span class="text-[10px] text-muted-foreground">Pagu:</span><input v-model.number="form.belanja_barang_pagu" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                        <div class="w-1/2"><span class="text-[10px] text-emerald-600 font-bold">Realisasi:</span><input v-model.number="form.belanja_barang_realisasi" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-emerald-50 text-emerald-700 font-bold" /></div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-primary uppercase">3. Belanja Modal</label>
                    <div class="flex gap-2">
                        <div class="w-1/2"><span class="text-[10px] text-muted-foreground">Pagu:</span><input v-model.number="form.belanja_modal_pagu" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                        <div class="w-1/2"><span class="text-[10px] text-emerald-600 font-bold">Realisasi:</span><input v-model.number="form.belanja_modal_realisasi" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-emerald-50 text-emerald-700 font-bold" /></div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-indigo-200 bg-indigo-50/30 p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-indigo-800 border-b border-indigo-200 pb-2">B. BELANJA LANGGANAN DAYA & JASA (Rp)</h3>
                
                <div class="space-y-1">
                    <label class="text-xs font-bold text-primary uppercase">1. Listrik</label>
                    <div class="flex gap-2">
                        <div class="w-1/2"><span class="text-[10px] text-muted-foreground">Anggaran:</span><input v-model.number="form.belanja_listrik_pagu" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                        <div class="w-1/2"><span class="text-[10px] text-emerald-600 font-bold">Realisasi:</span><input v-model.number="form.belanja_listrik_realisasi" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-emerald-50 text-emerald-700 font-bold" /></div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-primary uppercase">2. Telepon</label>
                    <div class="flex gap-2">
                        <div class="w-1/2"><span class="text-[10px] text-muted-foreground">Anggaran:</span><input v-model.number="form.belanja_telpon_pagu" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                        <div class="w-1/2"><span class="text-[10px] text-emerald-600 font-bold">Realisasi:</span><input v-model.number="form.belanja_telpon_realisasi" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-emerald-50 text-emerald-700 font-bold" /></div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-primary uppercase">3. Internet</label>
                    <div class="flex gap-2">
                        <div class="w-1/2"><span class="text-[10px] text-muted-foreground">Anggaran:</span><input v-model.number="form.belanja_internet_pagu" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                        <div class="w-1/2"><span class="text-[10px] text-emerald-600 font-bold">Realisasi:</span><input v-model.number="form.belanja_internet_realisasi" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-emerald-50 text-emerald-700 font-bold" /></div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-primary uppercase">4. Pos / Giro</label>
                    <div class="flex gap-2">
                        <div class="w-1/2"><span class="text-[10px] text-muted-foreground">Anggaran:</span><input v-model.number="form.belanja_pos_pagu" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                        <div class="w-1/2"><span class="text-[10px] text-emerald-600 font-bold">Realisasi:</span><input v-model.number="form.belanja_pos_realisasi" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm bg-emerald-50 text-emerald-700 font-bold" /></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-orange-200 bg-orange-50/30 p-6 shadow-sm">
            <h3 class="font-bold text-orange-800 text-lg border-b border-orange-200 pb-2 mb-4">C. BAHAN MAKANAN (BAMA)</h3>
            
            <div class="grid md:grid-cols-2 gap-6 mb-4">
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Nama Penyedia Bama</label><input v-model="form.bama_nama_penyedia" type="text" placeholder="PT / CV..." class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Nomor Kontrak</label><input v-model="form.bama_nomor_kontrak" type="text" placeholder="No. Kontrak Pengadaan..." class="w-full h-9 border rounded px-3 text-sm bg-white" /></div>
            </div>

            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Pagu Anggaran Bama (Rp)</label><input v-model.number="form.bama_pagu_anggaran" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm font-bold bg-white" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-emerald-600 uppercase">Realisasi Anggaran Bama (Rp)</label><input v-model.number="form.bama_realisasi_anggaran" type="number" min="0" class="w-full h-9 border rounded px-3 text-sm font-bold bg-emerald-50 text-emerald-700" /></div>
            </div>

            <div class="bg-card p-4 rounded-lg border shadow-sm">
                <p class="text-xs font-bold text-primary mb-3">Berita Acara Serah Terima (BAST)</p>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="space-y-2"><label class="text-[10px] text-muted-foreground uppercase">Tanggal BAST</label><input v-model="form.bama_tanggal_bast" type="date" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                    <div class="space-y-2"><label class="text-[10px] text-muted-foreground uppercase">Nomor BAST</label><input v-model="form.bama_nomor_bast" type="text" class="w-full h-8 border rounded px-2 text-sm bg-white" /></div>
                    <div class="space-y-2"><label class="text-[10px] text-blue-600 font-bold uppercase">Upload File BAST (PDF)</label>
                        <input type="file" accept="application/pdf" @input="form.bama_berita_acara = $event.target.files[0]" class="w-full text-[10px] file:mr-2 file:rounded file:border-0 file:bg-blue-100 file:text-blue-700 file:px-2 file:py-1 cursor-pointer" />
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow-lg hover:opacity-90">Simpan Data Anggaran</button></div>
      </form>
    </div>
  </AppLayout>
</template>