<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ databookingrapat: any }>()

const form = useForm({
  _method: 'put',
  divisi_pemohon: props.databookingrapat.divisi_pemohon,
  ruangan: props.databookingrapat.ruangan,
  judul_rapat: props.databookingrapat.judul_rapat,
  tanggal_rapat: props.databookingrapat.tanggal_rapat,
  waktu_mulai: props.databookingrapat.waktu_mulai,
  waktu_selesai: props.databookingrapat.waktu_selesai,
  jumlah_peserta: props.databookingrapat.jumlah_peserta,
  fasilitas: props.databookingrapat.fasilitas || [],
  keterangan: props.databookingrapat.keterangan || '',
  status: props.databookingrapat.status,
  catatan_admin: props.databookingrapat.catatan_admin || '',
  surat_undangan: null as File | null,
})

const daftarFasilitas = ['Proyektor / Layar Tambahan', 'Perangkat Zoom Meeting', 'Sound System / Mic', 'Whiteboard / Flipchart', 'Snack / Konsumsi', 'Air Mineral'];
const daftarRuangan = ['Ruang Rapat Utama Kanwil (Kapasitas 50)', 'Aula Kanwil (Kapasitas 150)'];

function toggleFasilitas(item: string) {
    const index = form.fasilitas.indexOf(item);
    if (index > -1) form.fasilitas.splice(index, 1);
    else form.fasilitas.push(item);
}

function submit() {
  form.post(`/admin/data-booking-rapats/${props.databookingrapat.id}`, { forceFormData: true })
}
</script>

<template>
  <Head title="Kelola Booking Rapat" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-primary">Kelola & Approval Booking</h1>
            <p class="text-sm text-muted-foreground">{{ databookingrapat.judul_rapat }}</p>
        </div>
        <Link href="/admin/data-booking-rapats" class="border border-input bg-background px-4 py-2 rounded-md hover:bg-accent text-sm font-bold shadow-sm transition-colors">Kembali</Link>
      </div>

      <div v-if="form.errors.waktu_mulai" class="bg-red-100 border-l-4 border-red-600 text-red-800 p-4 rounded shadow-sm flex items-center gap-3">
          <span class="text-2xl">⚠️</span>
          <div><p class="font-bold">Gagal Update Jadwal!</p><p class="text-sm">{{ form.errors.waktu_mulai }}</p></div>
      </div>

      <form @submit.prevent="submit" class="grid lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-card border rounded-2xl p-6 shadow-sm">
                <h3 class="font-bold text-lg border-b pb-2 mb-4">Informasi Rapat</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Bidang / Divisi Pemohon</label><input v-model="form.divisi_pemohon" type="text" required class="w-full h-10 border rounded-lg px-3 text-sm bg-white focus:ring-primary" /></div>
                    <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Jumlah Peserta</label><input v-model.number="form.jumlah_peserta" type="number" min="1" required class="w-full h-10 border rounded-lg px-3 text-sm bg-white focus:ring-primary" /></div>
                    <div class="space-y-2 md:col-span-2"><label class="text-xs font-bold text-primary uppercase">Judul / Agenda Rapat</label><input v-model="form.judul_rapat" type="text" required class="w-full h-10 border rounded-lg px-3 text-sm bg-blue-50/50 focus:ring-primary font-medium" /></div>
                </div>
            </div>

            <div class="bg-indigo-50/30 border border-indigo-100 rounded-2xl p-6 shadow-sm">
                <h3 class="font-bold text-indigo-800 text-lg border-b border-indigo-200 pb-2 mb-4">Waktu & Lokasi</h3>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-indigo-700 uppercase">Pilih Ruangan</label>
                        <select v-model="form.ruangan" required class="w-full h-10 border rounded-lg px-3 text-sm bg-white font-bold text-indigo-900 shadow-sm focus:ring-indigo-500">
                            <option v-for="r in daftarRuangan" :key="r" :value="r">{{ r }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Tanggal</label><input v-model="form.tanggal_rapat" type="date" required class="w-full h-10 border rounded-lg px-3 text-sm bg-white" /></div>
                        <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Jam Mulai</label><input v-model="form.waktu_mulai" type="time" required class="w-full h-10 border rounded-lg px-3 text-sm bg-white" /></div>
                        <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Jam Selesai</label><input v-model="form.waktu_selesai" type="time" required class="w-full h-10 border rounded-lg px-3 text-sm bg-white" /></div>
                    </div>
                </div>
            </div>
            
            <div class="bg-card border rounded-2xl p-6 shadow-sm">
                <h3 class="font-bold text-orange-600 border-b pb-2 mb-4">Kebutuhan Fasilitas</h3>
                <div class="grid grid-cols-2 gap-2">
                    <label v-for="item in daftarFasilitas" :key="item" class="flex items-center space-x-3 bg-muted/20 p-2 rounded-lg border border-transparent hover:border-orange-200 cursor-pointer transition-colors">
                        <input type="checkbox" :checked="form.fasilitas.includes(item)" @change="toggleFasilitas(item)" class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                        <span class="text-sm font-medium text-foreground">{{ item }}</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-yellow-50/50 border-2 border-yellow-300 rounded-2xl p-6 shadow-md">
                <h3 class="font-black text-yellow-800 text-xl border-b border-yellow-200 pb-3 mb-4">🛡️ Panel Approval</h3>
                
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-yellow-800 uppercase tracking-wider">Ubah Status Booking</label>
                        <select v-model="form.status" required class="w-full h-12 border-2 rounded-xl px-4 text-sm bg-white font-black shadow-inner" :class="{
                            'border-yellow-400 text-yellow-700': form.status === 'Menunggu Persetujuan',
                            'border-emerald-500 text-emerald-700': form.status === 'Disetujui',
                            'border-slate-500 text-slate-700': form.status === 'Selesai',
                            'border-red-500 text-red-700': form.status === 'Ditolak'
                        }">
                            <option value="Menunggu Persetujuan">Menunggu Persetujuan</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>

                    <div v-if="form.status === 'Ditolak'" class="space-y-2 pt-2">
                        <label class="text-xs font-bold text-red-600 uppercase">Alasan Penolakan</label>
                        <textarea v-model="form.catatan_admin" rows="3" required placeholder="Jelaskan alasan ditolak (misal: ruang dipakai Kakanwil)..." class="w-full border-red-300 rounded-lg px-3 py-2 text-sm bg-red-50 focus:ring-red-500"></textarea>
                    </div>
                </div>

                <div class="mt-8 pt-4 border-t border-yellow-200">
                    <button type="submit" :disabled="form.processing" class="w-full bg-primary text-primary-foreground py-4 rounded-xl font-black text-lg shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">Simpan Keputusan</button>
                </div>
            </div>

            <div class="bg-card border rounded-2xl p-6 shadow-sm">
                <h3 class="font-bold text-emerald-700 border-b border-emerald-100 pb-2 mb-4">Ganti Nota Dinas / Undangan (PDF)</h3>
                <input type="file" accept="application/pdf" @input="form.surat_undangan = $event.target.files[0]" class="w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-emerald-600 file:text-white file:px-3 file:py-2 hover:file:bg-emerald-700 cursor-pointer" />
            </div>
        </div>

      </form>
    </div>
  </AppLayout>
</template>