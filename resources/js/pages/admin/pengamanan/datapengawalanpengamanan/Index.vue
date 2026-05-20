<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ 
    datapengawalanpengamanans: any, 
    upts: any[], 
    filters: { tanggal?: string, upt_id?: string } 
}>()

const filterUpt = ref(props.filters.upt_id || '')
const filterTanggal = ref(props.filters.tanggal || '')
const isModalOpen = ref(false)
const selectedData = ref<any | null>(null)

function showDetail(data: any) { 
    selectedData.value = data; 
    isModalOpen.value = true; 
}

function closeModal() { 
    isModalOpen.value = false; 
    setTimeout(() => { selectedData.value = null }, 200); 
}

watch([filterUpt, filterTanggal], debounce(([newUpt, newTanggal]) => {
  const params = new URLSearchParams();
  if (newUpt) params.append('upt_id', newUpt);
  if (newTanggal) params.append('tanggal', newTanggal);
  router.get(window.location.pathname, Object.fromEntries(params), { 
      preserveState: true, preserveScroll: true, replace: true 
  });
}, 300))

function destroyData(id: number) {
  if (!confirm('Yakin ingin menghapus data pengawalan ini?')) return
  router.delete(`/admin/data-pengawalan-pengamanans/${id}`)
}
</script>

<template>
  <Head title="Pengawalan Luar" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-primary">Data Pengawalan Luar Tembok</h1>
            <p class="text-sm text-muted-foreground">Kelola laporan pengawalan berobat, asimilasi, dan sidang.</p>
        </div>
        <Link href="/admin/data-pengawalan-pengamanans/create" class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground px-4 py-2 text-sm font-bold shadow-sm hover:opacity-90 transition-opacity">
            + Lapor Pengawalan
        </Link>
      </div>

      <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
        <input v-model="filterTanggal" type="date" class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm cursor-pointer" />
        <select v-model="filterUpt" class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm cursor-pointer">
          <option value="">Semua Lapas / UPT</option>
          <option v-for="upt in upts" :key="upt.id" :value="String(upt.id)">{{ upt.name }}</option>
        </select>
      </div>

      <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm overflow-hidden">
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm">
            <thead class="bg-muted/50 border-b border-border">
              <tr>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">Tgl Pelaksanaan</th>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">Lokasi UPT</th>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">Jenis</th>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">Rasio (WBP vs Petugas)</th>
                <th class="h-12 px-4 text-right font-bold text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datapengawalanpengamanans.data" :key="d.id" class="border-b transition-colors hover:bg-muted/30">
                <td class="p-4">
                    <span class="font-bold">{{ d.tanggal_pelaksanaan }}</span>
                    <span class="text-xs text-muted-foreground block mt-0.5">{{ d.waktu_pelaksanaan_mulai }} - {{ d.waktu_pelaksanaan_selesai }}</span>
                </td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4">
                    <span class="inline-flex items-center bg-blue-100 text-blue-800 px-2 py-1 rounded-md text-xs font-bold">
                        {{ d.jenis_pengawalan?.nama_pengawalan || '-' }}
                    </span>
                </td>
                <td class="p-4">
                    <div class="flex items-center gap-2">
                        <span class="text-orange-600 font-bold bg-orange-50 px-2 py-0.5 rounded border border-orange-100">{{ d.jumlah_wbp }} WBP</span> 
                        <span class="text-muted-foreground text-[10px]">➔</span> 
                        <span class="text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">{{ d.jumlah_petugas }} Petugas</span>
                    </div>
                </td>
                <td class="p-4 text-right align-middle">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="inline-flex items-center px-3 py-1 border border-input rounded-md hover:bg-accent text-xs font-medium transition-colors">Lihat Detail</button>
                    <Link :href="`/admin/data-pengawalan-pengamanans/${d.id}/edit`" class="inline-flex items-center px-3 py-1 border border-input rounded-md hover:bg-accent text-xs font-medium transition-colors">Edit</Link>
                    <button @click="destroyData(d.id)" class="inline-flex items-center px-3 py-1 bg-destructive text-white rounded-md text-xs font-bold hover:bg-destructive/90 transition-colors">Hapus</button>
                  </div>
                </td>
              </tr>
              <tr v-if="!datapengawalanpengamanans.data.length">
                  <td colspan="5" class="p-8 text-center text-muted-foreground italic">Belum ada data pengawalan luar tembok.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm transition-opacity" @click.self="closeModal">
      <div class="w-full max-w-4xl rounded-xl bg-card shadow-xl flex flex-col max-h-[90vh] overflow-hidden border border-border">
        
        <div class="flex justify-between items-center border-b border-border p-5 bg-muted/20 sticky top-0 z-10">
            <div>
                <h2 class="text-lg font-black text-primary">Detail Laporan Pengawalan</h2>
                <p class="text-sm text-muted-foreground">{{ selectedData?.upt?.name }}</p>
            </div>
            <button @click="closeModal" class="text-2xl leading-none text-muted-foreground hover:text-red-500 transition-colors">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar">
            
            <div class="grid md:grid-cols-3 gap-4 border-b border-border pb-6">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Waktu Pelaksanaan</p>
                    <p class="text-sm font-bold text-foreground">{{ selectedData?.tanggal_pelaksanaan }}</p>
                    <p class="text-xs text-muted-foreground">{{ selectedData?.waktu_pelaksanaan_mulai }} s/d {{ selectedData?.waktu_pelaksanaan_selesai }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Jenis & Dasar Pengawalan</p>
                    <p class="text-sm font-bold text-primary">{{ selectedData?.jenis_pengawalan?.nama_pengawalan }}</p>
                    <p class="text-xs text-muted-foreground">No. SP: <span class="font-medium text-foreground">{{ selectedData?.surat_perintah || '-' }}</span></p>
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Sarana & Risiko WBP</p>
                    <p class="text-sm text-foreground">{{ selectedData?.sarana_pengawalan || '-' }}</p>
                    <p class="mt-1"><span class="text-[10px] bg-red-100 text-red-700 font-bold inline-block px-2 py-0.5 rounded-full border border-red-200">Risiko: {{ selectedData?.jenis_klasifikasi?.nama_klasifikasi || 'Tidak Diketahui' }}</span></p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="border rounded-xl p-5 bg-orange-50/30 border-orange-100 shadow-sm">
                    <div class="flex items-center justify-between border-b border-orange-200 pb-3 mb-3">
                        <p class="text-sm font-bold text-orange-800">Daftar WBP yang Dikawal</p>
                        <span class="bg-orange-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ selectedData?.jumlah_wbp }} Orang</span>
                    </div>
                    <ul v-if="selectedData?.detail_wbp_dikawal && selectedData?.detail_wbp_dikawal.length > 0" class="text-sm space-y-2 text-orange-900">
                        <li v-for="(k, i) in selectedData?.detail_wbp_dikawal" :key="'wbp'+i" class="flex items-start gap-2">
                            <span class="font-bold text-orange-400 mt-0.5">{{ i + 1 }}.</span>
                            <span>{{ k }}</span>
                        </li>
                    </ul>
                    <p v-else class="text-xs text-muted-foreground italic text-center py-2">Data nama WBP tidak dilampirkan.</p>
                </div>

                <div class="border rounded-xl p-5 bg-emerald-50/30 border-emerald-100 shadow-sm">
                    <div class="flex items-center justify-between border-b border-emerald-200 pb-3 mb-3">
                        <p class="text-sm font-bold text-emerald-800">Petugas Pengawal</p>
                        <span class="bg-emerald-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ selectedData?.jumlah_petugas }} Orang</span>
                    </div>
                    <ul v-if="selectedData?.detail_petugas_pengawalan && selectedData?.detail_petugas_pengawalan.length > 0" class="text-sm space-y-2 text-emerald-900">
                        <li v-for="(b, i) in selectedData?.detail_petugas_pengawalan" :key="'ptg'+i" class="flex items-start gap-2">
                            <span class="font-bold text-emerald-400 mt-0.5">{{ i + 1 }}.</span>
                            <span>{{ b }}</span>
                        </li>
                    </ul>
                    <p v-else class="text-xs text-muted-foreground italic text-center py-2">Data nama petugas tidak dilampirkan.</p>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-muted-foreground mb-3 border-b border-border pb-2">LAMPIRAN DOKUMEN</h3>
                <div class="grid md:grid-cols-3 gap-4">
                    
                    <div class="bg-orange-50 p-4 rounded-xl flex flex-col items-center text-center border border-orange-200 shadow-sm hover:shadow-md transition-shadow">
                        <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 mb-2 text-xl">📜</div>
                        <h4 class="font-bold text-sm text-orange-800 mb-3">Surat Perintah</h4>
                        <a v-if="selectedData?.surat_perintah" :href="`/storage/${selectedData?.surat_perintah}`" target="_blank" class="bg-orange-600 text-white px-4 py-2 rounded-md text-xs font-bold shadow hover:bg-orange-700 w-full transition-colors">Buka SP (PDF)</a>
                        <span v-else class="text-xs text-muted-foreground italic">File tidak ada</span>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-xl flex flex-col items-center text-center border border-blue-200 shadow-sm hover:shadow-md transition-shadow">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mb-2 text-xl">📄</div>
                        <h4 class="font-bold text-sm text-blue-800 mb-3">Laporan Resmi</h4>
                        <a v-if="selectedData?.laporan_pelaksanaan_pengawalan" :href="`/storage/${selectedData?.laporan_pelaksanaan_pengawalan}`" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-md text-xs font-bold shadow hover:bg-blue-700 w-full transition-colors">Buka Laporan (PDF)</a>
                        <span v-else class="text-xs text-muted-foreground italic">File tidak ada</span>
                    </div>

                    <div class="bg-indigo-50 p-4 rounded-xl flex flex-col items-center text-center border border-indigo-200 shadow-sm hover:shadow-md transition-shadow">
                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mb-2 text-xl">🖼️</div>
                        <h4 class="font-bold text-sm text-indigo-800 mb-3">Foto Kegiatan</h4>
                        <a v-if="selectedData?.dokumentasi" :href="`/storage/${selectedData?.dokumentasi}`" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-xs font-bold shadow hover:bg-indigo-700 w-full transition-colors">Lihat Foto</a>
                        <span v-else class="text-xs text-muted-foreground italic">File tidak ada</span>
                    </div>

                </div>
            </div>

        </div>
        
        <div class="border-t border-border p-4 bg-muted/10 flex justify-end">
            <button @click="closeModal" class="px-6 py-2 bg-secondary text-secondary-foreground rounded-md text-sm font-medium hover:bg-secondary/80 transition-colors">Tutup Jendela</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Untuk membuat scrollbar di dalam modal tidak terlalu tebal */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
</style>