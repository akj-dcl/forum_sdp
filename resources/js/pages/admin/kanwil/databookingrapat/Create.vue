<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  divisi_pemohon: '',
  ruangan: '',
  judul_rapat: '',
  tanggal_rapat: '',
  waktu_mulai: '',
  waktu_selesai: '',
  jumlah_peserta: 0,
  fasilitas: [] as string[],
  keterangan: '',
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
  form.post('/admin/data-booking-rapats')
}
</script>

<template>
  <Head title="Booking Ruang Rapat" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-primary">Reservasi Ruang Rapat</h1>
            <p class="text-sm text-muted-foreground">Sistem pintar penjadwalan ruang rapat Kanwil terintegrasi.</p>
        </div>
        <Link href="/admin/data-booking-rapats" class="border border-input bg-background px-4 py-2 rounded-md hover:bg-accent text-sm font-bold shadow-sm transition-colors">Batal</Link>
      </div>

      <div v-if="form.errors.waktu_mulai" class="bg-red-100 border-l-4 border-red-600 text-red-800 p-4 rounded shadow-sm flex items-center gap-3">
          <span class="text-2xl">⚠️</span>
          <div>
              <p class="font-bold">Gagal Booking!</p>
              <p class="text-sm">{{ form.errors.waktu_mulai }}</p>
          </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6 mt-2">
        <div class="grid lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-card border rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-lg border-b pb-2 mb-4">Informasi Rapat</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Bidang / Divisi Pemohon</label><input v-model="form.divisi_pemohon" type="text" placeholder="Contoh: Divisi Pemasyarakatan" required class="w-full h-10 border rounded-lg px-3 text-sm bg-white focus:ring-primary" /></div>
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
                                <option value="">-- Pilih Ruangan --</option>
                                <option v-for="r in daftarRuangan" :key="r" :value="r">{{ r }}</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Tanggal Rapat</label><input v-model="form.tanggal_rapat" type="date" required class="w-full h-10 border rounded-lg px-3 text-sm bg-white" /></div>
                            <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Jam Mulai</label><input v-model="form.waktu_mulai" type="time" required class="w-full h-10 border rounded-lg px-3 text-sm bg-white" /></div>
                            <div class="space-y-2"><label class="text-xs font-bold text-muted-foreground uppercase">Jam Selesai</label><input v-model="form.waktu_selesai" type="time" required class="w-full h-10 border rounded-lg px-3 text-sm bg-white" /></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card border rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-orange-600 border-b pb-2 mb-4">Kebutuhan Fasilitas</h3>
                    <div class="space-y-2">
                        <label v-for="item in daftarFasilitas" :key="item" class="flex items-center space-x-3 bg-muted/20 p-2 rounded-lg border border-transparent hover:border-orange-200 cursor-pointer transition-colors">
                            <input type="checkbox" :checked="form.fasilitas.includes(item)" @change="toggleFasilitas(item)" class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                            <span class="text-sm font-medium text-foreground">{{ item }}</span>
                        </label>
                    </div>
                    <div class="mt-4 space-y-2">
                        <label class="text-xs font-bold text-muted-foreground uppercase">Catatan Tambahan</label>
                        <textarea v-model="form.keterangan" rows="2" placeholder="Keperluan khusus lainnya..." class="w-full border rounded-lg px-3 py-2 text-sm bg-white"></textarea>
                    </div>
                </div>

                <div class="bg-card border rounded-2xl p-6 shadow-sm border-emerald-100">
                    <h3 class="font-bold text-emerald-700 border-b border-emerald-100 pb-2 mb-4">Nota Dinas / Undangan (Opsional)</h3>
                    <input type="file" accept="application/pdf" @input="form.surat_undangan = $event.target.files[0]" class="w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-emerald-600 file:text-white file:px-3 file:py-2 hover:file:bg-emerald-700 cursor-pointer" />
                </div>
            </div>

        </div>

        <div class="text-right pt-4 border-t"><button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-10 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5">Submit Booking Ruangan</button></div>
      </form>
    </div>
  </AppLayout>
</template>