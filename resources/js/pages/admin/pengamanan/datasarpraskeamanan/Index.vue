<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ datasarpraskeamanans: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

const filterUpt = ref(props.filters.upt_id || '')
const filterTanggal = ref(props.filters.tanggal || '')
const isModalOpen = ref(false)
const selectedData = ref<any | null>(null)

function showDetail(data: any) { selectedData.value = data; isModalOpen.value = true; }
function closeModal() { isModalOpen.value = false; setTimeout(() => { selectedData.value = null }, 200); }

watch([filterUpt, filterTanggal], debounce(([newUpt, newTanggal]) => {
  const params = new URLSearchParams();
  if (newUpt) params.append('upt_id', newUpt);
  if (newTanggal) params.append('tanggal', newTanggal);
  router.get(window.location.pathname, Object.fromEntries(params), { preserveState: true, preserveScroll: true, replace: true });
}, 300))

function destroyData(id: number) {
  if (!confirm('Hapus laporan sarpras keamanan ini?')) return
  router.delete(`/admin/data-sarpras-keamanans/${id}`)
}
</script>

<template>
  <Head title="Sarpras Keamanan" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight text-primary">Laporan Sarpras Keamanan</h1></div>
        <Link href="/admin/data-sarpras-keamanans/create" class="inline-flex items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-bold shadow">+ Lapor Inventaris</Link>
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
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">Tgl Laporan</th>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">UPT Pelapor</th>
                <th class="h-12 px-4 text-center font-bold text-muted-foreground">Status Senjata (Baik/Rusak)</th>
                <th class="h-12 px-4 text-right font-bold text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datasarpraskeamanans.data" :key="d.id" class="border-b hover:bg-muted/30">
                <td class="p-4 font-bold">{{ d.tanggal_input }}</td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 text-center">
                    <span class="text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded">{{ d.jumlah_senjata_api_baik }} Baik</span> / 
                    <span class="text-red-600 font-bold bg-red-50 px-2 py-1 rounded">{{ d.jumlah_senjata_api_rusak }} Rusak</span>
                </td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Detail</button>
                    <Link :href="`/admin/data-sarpras-keamanans/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
      <div class="w-full max-w-2xl rounded-xl bg-card shadow-lg flex flex-col max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center border-b p-5 bg-muted/20">
            <h2 class="text-lg font-black">Detail Inventaris: {{ selectedData?.upt?.name }}</h2>
            <button @click="closeModal" class="text-2xl leading-none text-muted-foreground hover:text-red-500">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            <div class="grid grid-cols-2 gap-4">
                
                <div class="border rounded-lg p-4 bg-card shadow-sm space-y-3">
                    <p class="text-xs text-muted-foreground border-b pb-2 mb-2 font-bold uppercase">Senjata & Keamanan</p>
                    <div class="flex justify-between items-center text-sm">
                        <span>1. Senjata Api</span> 
                        <span class="font-bold text-xs"><span class="text-emerald-600 bg-emerald-50 px-1 rounded">{{ selectedData?.jumlah_senjata_api_baik }} Baik</span> | <span class="text-red-600 bg-red-50 px-1 rounded">{{ selectedData?.jumlah_senjata_api_rusak }} Rusak</span></span>
                    </div>
                    <div class="flex justify-between items-center text-sm"><span>2. Amunisi</span> <span class="font-bold">{{ selectedData?.jumlah_amunisi }}</span></div>
                    <div class="flex justify-between items-center text-sm"><span>5. PHH</span> <span class="font-bold">{{ selectedData?.jumlah_phh }}</span></div>
                    <div class="flex justify-between items-center text-sm"><span>6. Borgol</span> <span class="font-bold">{{ selectedData?.jumlah_borgol }}</span></div>
                </div>

                <div class="border rounded-lg p-4 bg-card shadow-sm space-y-3">
                    <p class="text-xs text-muted-foreground border-b pb-2 mb-2 font-bold uppercase">Deteksi & Perlengkapan</p>
                    <div class="flex justify-between items-center text-sm"><span>3. X-Ray</span> <span class="font-bold">{{ selectedData?.jumlah_xray }}</span></div>
                    <div class="flex justify-between items-center text-sm"><span>4. Body Scanner</span> <span class="font-bold">{{ selectedData?.jumlah_body_scanner }}</span></div>
                    <div class="flex justify-between items-center text-sm"><span>7. Metal Detector</span> <span class="font-bold">{{ selectedData?.jumlah_metal_detector }}</span></div>
                    <div class="flex justify-between items-center text-sm"><span>8. CCTV</span> <span class="font-bold">{{ selectedData?.jumlah_cctv }}</span></div>
                    <div class="flex justify-between items-center text-sm"><span>9. APAR</span> <span class="font-bold">{{ selectedData?.jumlah_apar }}</span></div>
                    <div class="flex justify-between items-center text-sm"><span>10. Lonceng</span> <span class="font-bold">{{ selectedData?.jumlah_lonceng }}</span></div>
                    <div class="flex justify-between items-center text-sm"><span>11. HT</span> <span class="font-bold">{{ selectedData?.jumlah_ht }}</span></div>
                </div>

            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>