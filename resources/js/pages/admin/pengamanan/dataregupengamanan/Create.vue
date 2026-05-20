<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ upts: any[] }>()

const form = useForm({
  upt_id: props.upts?.[0]?.id ? String(props.upts[0].id) : '',
  tanggal_input: new Date().toISOString().split('T')[0],
  shift_rupam: '',
  nama_regu: '',
  waktu_tugas_mulai: '',
  waktu_tugas_selesai: '',
  jumlah_regu_jaga: 0,
  jumlah_hadir: 0,
  jumlah_tidak_hadir: 0,
  nama_komandan_jaga: '',
  nama_wadan_jaga: '',
  nama_p2u: '',
  nama_petugas_penjagaan: '',
  jumlah_petugas_luar: 0,
  jenis_bantuan: '',
  jumlah_bantuan: 0,
  jumlah_titik_pos: 0,
  pos_pengamanan_diisi: '',
  pos_pengamanan_tidak_diisi: '',
  dokumentasi: null as File | null,
})

function submit() {
  form.post('/admin/data-regu-pengamanans')
}
</script>

<template>
  <Head title="Input Regu Pengamanan" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-bold tracking-tight text-primary">Input Laporan Regu Pengamanan</h1>
        <Link href="/admin/data-regu-pengamanans" class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border bg-card p-5 shadow-sm grid md:grid-cols-4 gap-4">
            <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Tanggal Laporan</label><input v-model="form.tanggal_input" type="date" required class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
            <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Lokasi UPT</label>
                <select v-model="form.upt_id" required class="w-full h-9 border rounded px-3 text-sm bg-background"><option v-for="u in upts" :value="String(u.id)">{{ u.name }}</option></select>
            </div>
            <div class="space-y-2"><label class="text-xs font-bold text-primary uppercase">Shift Tugas</label>
                <select v-model="form.shift_rupam" required class="w-full h-9 border rounded px-3 text-sm bg-blue-50 font-bold text-blue-700">
                    <option value="">-- Pilih Shift --</option>
                    <option value="Pagi">Shift Pagi</option>
                    <option value="Siang">Shift Siang</option>
                    <option value="Malam">Shift Malam</option>
                </select>
            </div>
            <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Nama Regu</label><input v-model="form.nama_regu" type="text" placeholder="Contoh: Regu I" required class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
            
            <div class="md:col-span-2 space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Waktu Mulai Tugas</label><input v-model="form.waktu_tugas_mulai" type="time" required class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
            <div class="md:col-span-2 space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Waktu Selesai Tugas</label><input v-model="form.waktu_tugas_selesai" type="time" required class="w-full h-9 border rounded px-3 text-sm bg-background" /></div>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50/30 p-5 shadow-sm">
            <h3 class="font-bold text-blue-800 border-b border-blue-200 pb-2 mb-4">DATA KEHADIRAN ANGGOTA REGU</h3>
            <div class="grid grid-cols-3 gap-6">
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Total Regu Jaga</label><input v-model.number="form.jumlah_regu_jaga" type="number" min="0" class="w-full h-10 border rounded-md px-3 text-lg font-bold text-center bg-white" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-emerald-600">Jumlah Hadir</label><input v-model.number="form.jumlah_hadir" type="number" min="0" class="w-full h-10 border rounded-md px-3 text-lg font-bold text-center bg-emerald-50 text-emerald-700" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-red-600">Jumlah Tidak Hadir</label><input v-model.number="form.jumlah_tidak_hadir" type="number" min="0" class="w-full h-10 border rounded-md px-3 text-lg font-bold text-center bg-red-50 text-red-700" /></div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-primary border-b pb-2">Susunan Petugas</h3>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Komandan Jaga</label><input v-model="form.nama_komandan_jaga" type="text" class="w-full h-8 border rounded px-2 text-sm" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Wadan Jaga</label><input v-model="form.nama_wadan_jaga" type="text" class="w-full h-8 border rounded px-2 text-sm" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Petugas P2U</label><input v-model="form.nama_p2u" type="text" class="w-full h-8 border rounded px-2 text-sm" /></div>
                <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Petugas Penjagaan Lainnya</label><textarea v-model="form.nama_petugas_penjagaan" rows="2" class="w-full border rounded px-2 py-1 text-sm"></textarea></div>
                <div class="grid grid-cols-2 gap-4 pt-2 border-t mt-2">
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground">Petugas Luar</label><input v-model.number="form.jumlah_petugas_luar" type="number" min="0" class="w-full h-8 border rounded px-2 text-sm" /></div>
                    
                    <div class="bg-orange-50 p-2 rounded border border-orange-100 space-y-2">
                        <label class="text-xs font-bold text-orange-700">Bantuan Keamanan</label>
                        <select v-model="form.jenis_bantuan" class="w-full h-7 border rounded text-xs px-1">
                            <option value="">-- Tanpa Bantuan --</option>
                            <option value="TNI">TNI</option>
                            <option value="POLRI">POLRI</option>
                            <option value="TNI & POLRI">TNI & POLRI</option>
                        </select>
                        <input v-if="form.jenis_bantuan" v-model.number="form.jumlah_bantuan" type="number" min="1" placeholder="Jml Orang" class="w-full h-7 border rounded px-2 text-xs" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-emerald-600 border-b pb-2">Titik Pos Pengamanan</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground">Total Titik Pos</label>
                    <input v-model.number="form.jumlah_titik_pos" type="number" min="0" class="w-full h-10 border rounded px-3 text-lg font-bold bg-muted/20" />
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-emerald-600">Pos yang Diisi</label>
                    <textarea v-model="form.pos_pengamanan_diisi" rows="3" placeholder="Sebutkan pos mana saja... (Contoh: Pos 1, Pos 2)" class="w-full border rounded-md px-3 py-2 text-sm bg-emerald-50/30"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-red-500">Pos yang TIDAK Diisi</label>
                    <textarea v-model="form.pos_pengamanan_tidak_diisi" rows="3" placeholder="Sebutkan jika ada yang kosong..." class="w-full border rounded-md px-3 py-2 text-sm bg-red-50/30"></textarea>
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-blue-50 p-6 shadow-sm border-blue-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-blue-800">Upload Dokumen Laporan (PDF)</h3>
                <p class="text-xs text-blue-600 mt-1">Laporan resmi regu pengamanan.</p>
            </div>
            <input type="file" accept="application/pdf" @input="form.dokumentasi = $event.target.files[0]" required class="text-sm file:mr-3 file:rounded file:border-0 file:bg-blue-600 file:text-white file:px-4 file:py-2 hover:file:bg-blue-700 cursor-pointer" />
        </div>

        <div class="text-right"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow-lg hover:opacity-90">Simpan Laporan Regu</button></div>
      </form>
    </div>
  </AppLayout>
</template>