<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ databookingrapats: any, filters: { tanggal?: string, status?: string } }>()

const filterTanggal = ref(props.filters.tanggal || '')
const filterStatus = ref(props.filters.status || '')
const isModalOpen = ref(false)
const selectedData = ref<any | null>(null)

function showDetail(data: any) { selectedData.value = data; isModalOpen.value = true; }
function closeModal() { isModalOpen.value = false; setTimeout(() => { selectedData.value = null }, 200); }

watch([filterTanggal, filterStatus], debounce(([newTanggal, newStatus]) => {
  const params = new URLSearchParams();
  if (newTanggal) params.append('tanggal', newTanggal);
  if (newStatus) params.append('status', newStatus);
  router.get(window.location.pathname, Object.fromEntries(params), { preserveState: true, preserveScroll: true, replace: true });
}, 300))

function destroyData(id: number) {
  if (!confirm('Hapus reservasi rapat ini?')) return
  router.delete(`/admin/data-booking-rapats/${id}`)
}
</script>

<template>
  <Head title="Booking Rapat" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-primary">Jadwal & Reservasi Ruang Rapat</h1>
            <p class="text-sm text-muted-foreground">Sistem Manajemen Penggunaan Ruangan Kanwil NTB.</p>
        </div>
        <Link href="/admin/data-booking-rapats/create" class="inline-flex items-center justify-center rounded-xl bg-primary text-white px-6 py-3 text-sm font-black shadow-md hover:bg-primary/90 hover:shadow-lg transition-all">+ Booking Ruangan Baru</Link>
      </div>

      <div class="flex gap-3">
        <input v-model="filterTanggal" type="date" class="h-10 rounded-lg border border-input bg-background px-4 py-2 text-sm shadow-sm" />
        <select v-model="filterStatus" class="h-10 rounded-lg border border-input bg-background px-4 py-2 text-sm shadow-sm font-semibold" :class="{
            'text-yellow-600': filterStatus === 'Menunggu Persetujuan',
            'text-emerald-600': filterStatus === 'Disetujui',
            'text-slate-600': filterStatus === 'Selesai',
            'text-red-600': filterStatus === 'Ditolak'
        }">
          <option value="" class="text-foreground">Semua Status Booking</option>
          <option value="Menunggu Persetujuan" class="text-yellow-600">Menunggu Persetujuan</option>
          <option value="Disetujui" class="text-emerald-600">Disetujui (Akan Datang)</option>
          <option value="Selesai" class="text-slate-600">Selesai</option>
          <option value="Ditolak" class="text-red-600">Ditolak</option>
        </select>
      </div>

      <div class="rounded-2xl border border-border bg-card text-card-foreground shadow-sm overflow-hidden mt-2">
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm">
            <thead class="bg-muted/50 border-b border-border">
              <tr>
                <th class="h-14 px-6 text-left font-black text-muted-foreground uppercase text-[10px] tracking-wider w-40">Jadwal Rapat</th>
                <th class="h-14 px-6 text-left font-black text-muted-foreground uppercase text-[10px] tracking-wider">Pemohon & Agenda</th>
                <th class="h-14 px-6 text-left font-black text-muted-foreground uppercase text-[10px] tracking-wider">Lokasi Ruangan</th>
                <th class="h-14 px-6 text-center font-black text-muted-foreground uppercase text-[10px] tracking-wider">Status Approval</th>
                <th class="h-14 px-6 text-right font-black text-muted-foreground uppercase text-[10px] tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in databookingrapats.data" :key="d.id" class="border-b transition-colors hover:bg-muted/30">
                <td class="p-6">
                    <div class="font-black text-lg">{{ d.tanggal_rapat.split('-')[2] }}/{{ d.tanggal_rapat.split('-')[1] }}</div>
                    <div class="text-xs font-bold text-primary bg-primary/10 px-2 py-0.5 rounded inline-block mt-1">{{ d.waktu_mulai.substring(0,5) }} - {{ d.waktu_selesai.substring(0,5) }}</div>
                </td>
                <td class="p-6">
                    <div class="font-bold text-base max-w-sm truncate" :title="d.judul_rapat">{{ d.judul_rapat }}</div>
                    <div class="text-xs text-muted-foreground mt-1 flex items-center gap-1">🏢 <span class="font-semibold">{{ d.divisi_pemohon }}</span></div>
                </td>
                <td class="p-6">
                    <div class="font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-1.5 rounded-lg inline-block text-xs">
                        {{ d.ruangan.split('(')[0] }} </div>
                </td>
                <td class="p-6 text-center">
                    <span v-if="d.status === 'Menunggu Persetujuan'" class="bg-yellow-100 text-yellow-800 border border-yellow-200 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm">Menunggu</span>
                    <span v-if="d.status === 'Disetujui'" class="bg-emerald-100 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm">Disetujui</span>
                    <span v-if="d.status === 'Selesai'" class="bg-slate-100 text-slate-800 border border-slate-200 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm">Selesai</span>
                    <span v-if="d.status === 'Ditolak'" class="bg-red-100 text-red-800 border border-red-200 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm">Ditolak</span>
                </td>
                <td class="p-6 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-4 py-1.5 border border-input bg-background rounded-lg hover:bg-accent text-xs font-bold transition-all shadow-sm">Tiket</button>
                    <Link :href="`/admin/data-booking-rapats/${d.id}/edit`" class="px-4 py-1.5 bg-primary text-primary-foreground rounded-lg hover:opacity-90 text-xs font-bold transition-all shadow-sm">Kelola</Link>
                  </div>
                </td>
              </tr>
              <tr v-if="!databookingrapats.data.length">
                  <td colspan="5" class="p-12 text-center text-muted-foreground italic">Belum ada reservasi ruang rapat.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" @click.self="closeModal">
      <div class="w-full max-w-2xl bg-[#f8fafc] rounded-2xl shadow-2xl flex flex-col overflow-hidden relative">
        
        <div class="h-3 w-full bg-primary"></div>
        <div class="absolute right-8 top-8 opacity-10 text-8xl">🏢</div>
        
        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <span class="text-[10px] font-black tracking-widest uppercase text-muted-foreground mb-1 block">Status Reservasi</span>
                    <span v-if="selectedData?.status === 'Menunggu Persetujuan'" class="text-yellow-600 font-black text-xl">MENUNGGU PERSETUJUAN</span>
                    <span v-if="selectedData?.status === 'Disetujui'" class="text-emerald-600 font-black text-xl">RESERVASI DISETUJUI</span>
                    <span v-if="selectedData?.status === 'Ditolak'" class="text-red-600 font-black text-xl">RESERVASI DITOLAK</span>
                    <span v-if="selectedData?.status === 'Selesai'" class="text-slate-600 font-black text-xl">RAPAT SELESAI</span>
                </div>
                <button @click="closeModal" class="h-8 w-8 rounded-full bg-slate-200 text-slate-500 hover:bg-red-100 hover:text-red-600 flex items-center justify-center font-bold transition-colors">&times;</button>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 space-y-6 relative">
                <div class="absolute -left-3 top-1/2 w-6 h-6 bg-[#f8fafc] rounded-full"></div>
                <div class="absolute -right-3 top-1/2 w-6 h-6 bg-[#f8fafc] rounded-full"></div>
                
                <div class="text-center border-b border-dashed border-slate-200 pb-6">
                    <p class="text-xs font-bold text-slate-400 uppercase mb-2">Agenda Rapat</p>
                    <h3 class="text-2xl font-black text-slate-800 leading-tight px-8">{{ selectedData?.judul_rapat }}</h3>
                    <p class="text-sm font-bold text-primary mt-3">Pemohon: {{ selectedData?.divisi_pemohon }}</p>
                </div>

                <div class="grid grid-cols-2 gap-6 pt-2">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Jadwal Pelaksanaan</p>
                        <p class="font-black text-lg text-slate-800">{{ selectedData?.tanggal_rapat }}</p>
                        <p class="text-sm font-bold text-emerald-600">{{ selectedData?.waktu_mulai.substring(0,5) }} WIB - {{ selectedData?.waktu_selesai.substring(0,5) }} WIB</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Lokasi & Peserta</p>
                        <p class="font-black text-base text-indigo-700 leading-tight">{{ selectedData?.ruangan }}</p>
                        <p class="text-sm font-bold text-orange-600 mt-1">{{ selectedData?.jumlah_peserta }} Orang Peserta</p>
                    </div>
                </div>

                <div v-if="selectedData?.status === 'Ditolak'" class="bg-red-50 border border-red-100 p-4 rounded-lg mt-4">
                    <p class="text-[10px] font-bold text-red-600 uppercase mb-1">Alasan Penolakan</p>
                    <p class="text-sm font-medium text-red-900">{{ selectedData?.catatan_admin }}</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Fasilitas Diminta</p>
                    <div v-if="selectedData?.fasilitas && selectedData.fasilitas.length > 0" class="flex flex-wrap gap-2">
                        <span v-for="f in selectedData.fasilitas" :key="f" class="bg-slate-100 text-slate-700 px-2 py-1 rounded text-[10px] font-bold">{{ f }}</span>
                    </div>
                    <p v-else class="text-xs italic text-slate-400">Tidak ada tambahan.</p>
                </div>
                
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex flex-col justify-center items-center text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Dokumen Undangan</p>
                    <a v-if="selectedData?.surat_undangan" :href="`/storage/${selectedData.surat_undangan}`" target="_blank" class="bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white transition-colors w-full">Buka Dokumen (PDF)</a>
                    <p v-else class="text-xs italic text-slate-400">Tidak ada undangan dilampirkan.</p>
                </div>
            </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>