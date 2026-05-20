<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ datarealisasianggarans: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

const filterUpt = ref(props.filters.upt_id || '')
const filterTanggal = ref(props.filters.tanggal || '')
const isModalOpen = ref(false)
const selectedData = ref<any | null>(null)

function formatRp(angka: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka || 0);
}

function getTotalPagu(d: any) {
    if(!d) return 0;
    return d.belanja_pegawai_pagu + d.belanja_barang_pagu + d.belanja_modal_pagu + d.belanja_listrik_pagu + d.belanja_telpon_pagu + d.belanja_internet_pagu + d.belanja_pos_pagu + d.bama_pagu_anggaran;
}

function getTotalRealisasi(d: any) {
    if(!d) return 0;
    return d.belanja_pegawai_realisasi + d.belanja_barang_realisasi + d.belanja_modal_realisasi + d.belanja_listrik_realisasi + d.belanja_telpon_realisasi + d.belanja_internet_realisasi + d.belanja_pos_realisasi + d.bama_realisasi_anggaran;
}

function showDetail(data: any) { selectedData.value = data; isModalOpen.value = true; }
function closeModal() { isModalOpen.value = false; setTimeout(() => { selectedData.value = null }, 200); }

watch([filterUpt, filterTanggal], debounce(([newUpt, newTanggal]) => {
  const params = new URLSearchParams();
  if (newUpt) params.append('upt_id', newUpt);
  if (newTanggal) params.append('tanggal', newTanggal);
  router.get(window.location.pathname, Object.fromEntries(params), { preserveState: true, preserveScroll: true, replace: true });
}, 300))

function destroyData(id: number) {
  if (!confirm('Hapus laporan keuangan ini?')) return
  router.delete(`/admin/data-realisasi-anggarans/${id}`)
}
</script>

<template>
  <Head title="Realisasi Anggaran" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight text-primary">Data Realisasi Anggaran</h1></div>
        <Link href="/admin/data-realisasi-anggarans/create" class="inline-flex items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-bold shadow hover:bg-primary/90">+ Input Serapan DIPA</Link>
      </div>

      <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
        <input v-model="filterTanggal" type="date" class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm cursor-pointer" />
        <select v-model="filterUpt" class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm cursor-pointer">
          <option value="">Semua UPT</option>
          <option v-for="upt in upts" :key="upt.id" :value="String(upt.id)">{{ upt.name }}</option>
        </select>
      </div>

      <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm overflow-hidden">
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm">
            <thead class="bg-muted/50 border-b border-border">
              <tr>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground w-32">Bulan Laporan</th>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">UPT</th>
                <th class="h-12 px-4 text-right font-bold text-muted-foreground">Total Pagu</th>
                <th class="h-12 px-4 text-right font-bold text-emerald-700">Total Realisasi</th>
                <th class="h-12 px-4 text-right font-bold text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datarealisasianggarans.data" :key="d.id" class="border-b hover:bg-muted/30">
                <td class="p-4 font-bold">{{ d.tanggal_input }}</td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 text-right font-medium text-muted-foreground">{{ formatRp(getTotalPagu(d)) }}</td>
                <td class="p-4 text-right font-bold text-emerald-600 bg-emerald-50/50">{{ formatRp(getTotalRealisasi(d)) }}</td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Rincian</button>
                    <Link :href="`/admin/data-realisasi-anggarans/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
                    <button @click="destroyData(d.id)" class="px-3 py-1 bg-red-600 text-white rounded-md text-xs font-bold">Hapus</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @click.self="closeModal">
      <div class="w-full max-w-4xl rounded-xl bg-card shadow-lg flex flex-col max-h-[90vh] overflow-hidden border border-border">
        
        <div class="flex justify-between items-center border-b border-border p-5 bg-muted/20">
            <div>
                <h2 class="text-lg font-black text-primary">Detail Serapan Anggaran UPT</h2>
                <p class="text-sm text-muted-foreground">{{ selectedData?.upt?.name }} (Bulan: {{ selectedData?.tanggal_input }})</p>
            </div>
            <button @click="closeModal" class="text-2xl leading-none text-muted-foreground hover:text-red-500">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl text-center">
                    <p class="text-xs font-bold text-slate-500 uppercase mb-1">Total Pagu DIPA Keseluruhan</p>
                    <p class="text-2xl font-black text-slate-800">{{ formatRp(getTotalPagu(selectedData)) }}</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl text-center">
                    <p class="text-xs font-bold text-emerald-600 uppercase mb-1">Total Realisasi Belanja</p>
                    <p class="text-2xl font-black text-emerald-700">{{ formatRp(getTotalRealisasi(selectedData)) }}</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                
                <div class="space-y-4">
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-blue-50/80 px-4 py-2 border-b font-bold text-blue-800 text-xs uppercase">Belanja Pegawai, Barang, Modal</div>
                        <div class="p-3 text-sm space-y-2 bg-card">
                            <div class="flex justify-between"><span class="font-bold">1. Belanja Pegawai</span></div>
                            <div class="flex justify-between text-xs"><span class="text-muted-foreground pl-3">Pagu</span><span>{{ formatRp(selectedData?.belanja_pegawai_pagu) }}</span></div>
                            <div class="flex justify-between text-xs border-b border-dashed pb-2"><span class="text-emerald-600 pl-3">Realisasi</span><span class="font-bold text-emerald-700">{{ formatRp(selectedData?.belanja_pegawai_realisasi) }}</span></div>
                            
                            <div class="flex justify-between mt-2"><span class="font-bold">2. Belanja Barang</span></div>
                            <div class="flex justify-between text-xs"><span class="text-muted-foreground pl-3">Pagu</span><span>{{ formatRp(selectedData?.belanja_barang_pagu) }}</span></div>
                            <div class="flex justify-between text-xs border-b border-dashed pb-2"><span class="text-emerald-600 pl-3">Realisasi</span><span class="font-bold text-emerald-700">{{ formatRp(selectedData?.belanja_barang_realisasi) }}</span></div>
                            
                            <div class="flex justify-between mt-2"><span class="font-bold">3. Belanja Modal</span></div>
                            <div class="flex justify-between text-xs"><span class="text-muted-foreground pl-3">Pagu</span><span>{{ formatRp(selectedData?.belanja_modal_pagu) }}</span></div>
                            <div class="flex justify-between text-xs"><span class="text-emerald-600 pl-3">Realisasi</span><span class="font-bold text-emerald-700">{{ formatRp(selectedData?.belanja_modal_realisasi) }}</span></div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-indigo-50/80 px-4 py-2 border-b font-bold text-indigo-800 text-xs uppercase">Belanja Langganan Daya & Jasa</div>
                        <div class="p-3 text-sm space-y-2 bg-card">
                            <div class="flex justify-between"><span class="font-bold">Listrik</span></div>
                            <div class="flex justify-between text-xs"><span class="text-muted-foreground pl-3">Pagu</span><span>{{ formatRp(selectedData?.belanja_listrik_pagu) }}</span></div>
                            <div class="flex justify-between text-xs border-b border-dashed pb-2"><span class="text-emerald-600 pl-3">Realisasi</span><span class="font-bold text-emerald-700">{{ formatRp(selectedData?.belanja_listrik_realisasi) }}</span></div>
                            
                            <div class="flex justify-between mt-2"><span class="font-bold">Telepon</span></div>
                            <div class="flex justify-between text-xs border-b border-dashed pb-2"><span class="text-emerald-600 pl-3">Realisasi</span><span class="font-bold text-emerald-700">{{ formatRp(selectedData?.belanja_telpon_realisasi) }}</span></div>
                            
                            <div class="flex justify-between mt-2"><span class="font-bold">Internet</span></div>
                            <div class="flex justify-between text-xs border-b border-dashed pb-2"><span class="text-emerald-600 pl-3">Realisasi</span><span class="font-bold text-emerald-700">{{ formatRp(selectedData?.belanja_internet_realisasi) }}</span></div>
                            
                            <div class="flex justify-between mt-2"><span class="font-bold">Pos / Giro</span></div>
                            <div class="flex justify-between text-xs"><span class="text-emerald-600 pl-3">Realisasi</span><span class="font-bold text-emerald-700">{{ formatRp(selectedData?.belanja_pos_realisasi) }}</span></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="border rounded-lg p-4 bg-orange-50/30 border-orange-100 shadow-sm">
                <p class="text-sm font-bold text-orange-800 mb-3 border-b border-orange-200 pb-2">DATA BAHAN MAKANAN (BAMA)</p>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-muted-foreground">Penyedia: <span class="font-bold text-foreground">{{ selectedData?.bama_nama_penyedia || '-' }}</span></p>
                        <p class="text-xs text-muted-foreground mt-1">No. Kontrak: <span class="font-medium text-foreground">{{ selectedData?.bama_nomor_kontrak || '-' }}</span></p>
                        <p class="text-xs text-muted-foreground mt-1">Tgl / No. BAST: <span class="font-medium text-foreground">{{ selectedData?.bama_tanggal_bast || '-' }} ({{ selectedData?.bama_nomor_bast || '-' }})</span></p>
                    </div>
                    <div class="bg-white p-3 rounded border text-right">
                        <div class="flex justify-between items-center text-xs"><span class="text-muted-foreground">Pagu Bama:</span><span>{{ formatRp(selectedData?.bama_pagu_anggaran) }}</span></div>
                        <div class="flex justify-between items-center text-sm font-bold mt-1 pt-1 border-t"><span class="text-emerald-600">Realisasi Bama:</span><span class="text-emerald-700">{{ formatRp(selectedData?.bama_realisasi_anggaran) }}</span></div>
                    </div>
                </div>
                
                <div v-if="selectedData?.bama_berita_acara" class="mt-4 pt-3 border-t border-orange-200 flex justify-end">
                    <a :href="`/storage/${selectedData?.bama_berita_acara}`" target="_blank" class="bg-orange-600 text-white px-4 py-1.5 rounded text-xs font-bold shadow hover:bg-orange-700">Lihat File BAST (PDF)</a>
                </div>
            </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>