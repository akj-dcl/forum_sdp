<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

type Upt = { id: number; name: string }
type JenisPidana = { id: number; nama_pidana: string }

const props = defineProps<{ 
  dataresidivis: any, 
  upts: Upt[], 
  jenis_pidanas: JenisPidana[] 
}>()

const form = useForm({
  _method: 'put',
  upt_id: String(props.dataresidivis.upt_id),
  tanggal: props.dataresidivis.tanggal,
  nama: props.dataresidivis.nama,
  alamat: props.dataresidivis.alamat || '',
  
  jenis_pidana_sekarang_id: String(props.dataresidivis.jenis_pidana_sekarang_id),
  lama_pidana_sekarang: props.dataresidivis.lama_pidana_sekarang,
  tempat_pidana_sekarang: props.dataresidivis.tempat_pidana_sekarang,
  
  berapa_kali_dipidana: props.dataresidivis.berapa_kali_dipidana,
  putusan_pengadilan: null as File | null,
  
  jenis_pidana_sebelumnya_id: String(props.dataresidivis.jenis_pidana_sebelumnya_id),
  lama_pidana_sebelumnya: props.dataresidivis.lama_pidana_sebelumnya,
  tempat_pidana_sebelumnya: props.dataresidivis.tempat_pidana_sebelumnya,
  jenis_pembebasan_sebelumnya: props.dataresidivis.jenis_pembebasan_sebelumnya,
})

function submit() {
  form.transform((data) => ({
    ...data,
    upt_id: Number(data.upt_id),
    jenis_pidana_sekarang_id: Number(data.jenis_pidana_sekarang_id),
    jenis_pidana_sebelumnya_id: Number(data.jenis_pidana_sebelumnya_id),
  })).post(`/admin/data-residivises/${props.dataresidivis.id}`, {
    forceFormData: true, // 🛠️ WAJIB AGAR FILE PDF TERBACA SAAT UPDATE
    preserveScroll: true
  })
}
</script>

<template>
  <Head title="Edit Data Residivis" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-4">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight text-primary">Edit Data Residivis</h1>
          <p class="text-sm text-muted-foreground">Perbarui data riwayat pengulangan tindak pidana WBP.</p>
        </div>
        <Link href="/admin/data-residivises" class="inline-flex items-center justify-center rounded-md border bg-background px-4 py-2 text-sm font-medium hover:bg-accent">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
          <div class="rounded-xl border bg-card p-6 shadow-sm">
            <h3 class="font-bold text-lg border-b pb-2 mb-4">Informasi Dasar & Lokasi</h3>
            <div class="grid gap-6 md:grid-cols-2">
              <div class="space-y-2">
                <label class="text-sm font-bold text-muted-foreground">Lokasi UPT (Lapas)</label>
                <select v-model="form.upt_id" :disabled="upts.length === 1" required class="flex h-10 w-full rounded-md border bg-background px-3 py-2 text-sm">
                  <option v-for="u in upts" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
                </select>
              </div>
              <div class="space-y-2">
                  <label class="text-sm font-bold text-muted-foreground">Tanggal Pencatatan</label>
                  <input v-model="form.tanggal" type="date" required class="flex h-10 w-full rounded-md border bg-background px-3 py-2 text-sm" />
              </div>
              <div class="md:col-span-2 space-y-2">
                <label class="text-sm font-bold text-muted-foreground">Nama Lengkap Residivis</label>
                <input v-model="form.nama" type="text" required placeholder="Nama Lengkap / Alias WBP" class="flex h-10 w-full rounded-md border bg-background px-3 py-2 text-sm font-bold text-primary" />
              </div>
              <div class="md:col-span-2 space-y-2">
                <label class="text-sm font-bold text-muted-foreground">Alamat Lengkap (Opsional)</label>
                <textarea v-model="form.alamat" rows="2" class="flex w-full rounded-md border bg-background px-3 py-2 text-sm"></textarea>
              </div>
            </div>
          </div>

          <div class="grid lg:grid-cols-2 gap-6">
              <div class="rounded-xl border border-red-200 bg-red-50/30 p-6 shadow-sm space-y-4">
                  <h3 class="font-bold text-red-700 border-b border-red-200 pb-2">A. Pidana Saat Ini</h3>
                  <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground uppercase">Jenis Pidana</label>
                    <select v-model="form.jenis_pidana_sekarang_id" required class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm font-semibold">
                      <option v-for="jk in jenis_pidanas" :key="jk.id" :value="String(jk.id)">{{ jk.nama_pidana }}</option>
                    </select>
                  </div>
                  <div class="grid grid-cols-2 gap-4">
                      <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Lama Pidana (Bulan)</label><input v-model="form.lama_pidana_sekarang" type="number" required class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm" /></div>
                      <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Tempat Pidana</label><input v-model="form.tempat_pidana_sekarang" type="text" required class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm" /></div>
                  </div>
                  <div class="grid grid-cols-2 gap-4 pt-2 border-t border-red-200/50">
                      <div class="space-y-2">
                          <label class="text-xs font-bold text-red-600 uppercase">Telah Dipidana Ke-?</label>
                          <div class="flex items-center gap-2">
                              <input v-model.number="form.berapa_kali_dipidana" type="number" min="1" required class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm font-bold text-red-700" />
                              <span class="text-sm font-bold text-red-700">Kali</span>
                          </div>
                      </div>
                      
                      <div class="space-y-2">
                          <label class="text-xs font-bold text-blue-600 uppercase">Ganti Putusan Pengadilan</label>
                          <input type="file" accept="application/pdf" @input="form.putusan_pengadilan = $event.target.files[0]" class="w-full text-[10px] file:mr-2 file:rounded file:border-0 file:bg-blue-100 file:text-blue-700 file:px-2 file:py-1 cursor-pointer bg-white border p-1 rounded" />
                          <p class="text-[10px] mt-1 text-muted-foreground">Kosongkan jika tidak diganti.</p>
                          
                          <a v-if="props.dataresidivis.putusan_pengadilan" :href="`/view-file?path=${props.dataresidivis.putusan_pengadilan}`" target="_blank" class="text-xs text-blue-600 hover:underline mt-2 inline-flex items-center font-bold">
                              📄 Lihat Putusan Saat Ini
                          </a>
                      </div>
                  </div>
              </div>

              <div class="rounded-xl border border-orange-200 bg-orange-50/30 p-6 shadow-sm space-y-4">
                  <h3 class="font-bold text-orange-700 border-b border-orange-200 pb-2">B. Pidana Sebelumnya</h3>
                  <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground uppercase">Jenis Pidana Lama</label>
                    <select v-model="form.jenis_pidana_sebelumnya_id" required class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm">
                      <option v-for="jk in jenis_pidanas" :key="jk.id" :value="String(jk.id)">{{ jk.nama_pidana }}</option>
                    </select>
                  </div>
                  <div class="grid grid-cols-2 gap-4">
                      <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Lama Pidana Lama</label><input v-model="form.lama_pidana_sebelumnya" type="number" required class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm" /></div>
                      <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Tempat Pidana Lama</label><input v-model="form.tempat_pidana_sebelumnya" type="text" required class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm" /></div>
                  </div>
                  <div class="space-y-2 pt-2 border-t border-orange-200/50">
                      <label class="text-xs font-bold text-muted-foreground uppercase">Jenis Pembebasan Terakhir</label>
                      <input v-model="form.jenis_pembebasan_sebelumnya" type="text" placeholder="Contoh: Pembebasan Bersyarat (PB), Cuti Menjelang Bebas..." required class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm" />
                  </div>
              </div>
          </div>

          <div class="flex justify-end pt-4 pb-8">
            <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-xl bg-primary px-10 py-3 text-sm font-bold text-primary-foreground shadow hover:opacity-90 transition-all">Simpan Perubahan Data</button>
          </div>
      </form>
    </div>
  </AppLayout>
</template>