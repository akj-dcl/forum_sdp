<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ upts: any[] }>()

const form = useForm({
  upt_id: props.upts?.[0]?.id ? String(props.upts[0].id) : '',
  tanggal_input: new Date().toISOString().split('T')[0],
  nama_kegiatan: '',
  tanggal_publikasi: new Date().toISOString().split('T')[0], // Default hari ini
  uraian_singkat: '',
  media_publikasi: '',
  link_berita: '',
})

function submit() {
  form.post('/admin/data-publikasi-sdms')
}
</script>

<template>
  <Head title="Input Publikasi" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-primary">Input Data Publikasi</h1>
            <p class="text-sm text-muted-foreground">Catat dokumentasi berita dan kegiatan humas UPT (TU & Umum).</p>
        </div>
        <Link href="/admin/data-publikasi-sdms" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-bold">Tanggal Input</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
            <div class="space-y-2"><label class="text-sm font-bold">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50/20 p-6 shadow-sm">
            <h3 class="font-bold text-blue-800 text-lg border-b border-blue-200 pb-2 mb-4">DETAIL KEGIATAN & PUBLIKASI</h3>
            
            <div class="grid md:grid-cols-3 gap-6 mb-6">
                <div class="md:col-span-2 space-y-2">
                    <label class="text-xs font-bold text-primary uppercase">1. Nama Kegiatan</label>
                    <input v-model="form.nama_kegiatan" type="text" placeholder="Contoh: Razia Gabungan Blok Hunian..." required class="w-full h-10 border rounded-md px-3 text-sm bg-white" />
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground uppercase">2. Tanggal Publikasi</label>
                    <input v-model="form.tanggal_publikasi" type="date" required class="w-full h-10 border rounded-md px-3 text-sm bg-white" />
                </div>
            </div>

            <div class="space-y-2 mb-6">
                <label class="text-xs font-bold text-muted-foreground uppercase">3. Uraian Singkat Kegiatan</label>
                <textarea v-model="form.uraian_singkat" rows="4" placeholder="Jelaskan ringkasan kegiatan tersebut..." required class="w-full border rounded-md px-4 py-3 text-sm bg-white"></textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-6 pt-4 border-t border-blue-200/50">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-emerald-700 uppercase">4. Media Publikasi</label>
                    <select v-model="form.media_publikasi" required class="w-full h-10 border rounded-md px-3 text-sm bg-white focus:ring-emerald-500">
                        <option value="">-- Pilih Jenis Media --</option>
                        <option value="Media Internal (Web Lapas, IG Lapas)">Media Internal (Web/Medsos UPT)</option>
                        <option value="Media Eksternal (Berita Online, TV)">Media Eksternal (Berita Online/Cetak/TV)</option>
                        <option value="Media Internal & Eksternal">Keduanya (Internal & Eksternal)</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-indigo-700 uppercase">5. Link Berita (Media Sosial/Online)</label>
                    <input v-model="form.link_berita" type="text" placeholder="https://..." class="w-full h-10 border border-indigo-200 rounded-md px-3 text-sm bg-indigo-50/30 focus:ring-indigo-500" />
                    <p class="text-[10px] text-muted-foreground italic">Pastikan diawali dengan http:// atau https://</p>
                </div>
            </div>
        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow-lg hover:opacity-90">Simpan Data Publikasi</button></div>
      </form>
    </div>
  </AppLayout>
</template>