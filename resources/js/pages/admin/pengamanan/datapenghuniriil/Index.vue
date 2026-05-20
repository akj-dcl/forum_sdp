<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ datapenghuniriils: any, upts: any[], jenis_pengeluarans: any[], filters: { tanggal?: string, upt_id?: string } }>()

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
  if (!confirm('Hapus laporan riil ini?')) return
  router.delete(`/admin/data-penghuni-riils/${id}`)
}

function findPengeluaranName(id: string | number) {
    const j = props.jenis_pengeluarans.find(i => String(i.id) === String(id));
    return j ? j.nama_pengeluaran : '-';
}
</script>

<template>
  <Head title="Penghuni Riil" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-semibold tracking-tight">Data Penghuni Riil (Portir)</h1></div>
        <Link href="/admin/data-penghuni-riils/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">+ Laporan Baru</Link>
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
            <thead class="[&_tr]:border-b border-border bg-muted/30">
              <tr>
                <th class="h-12 px-4 text-left font-medium">Tanggal</th>
                <th class="h-12 px-4 text-left font-medium">UPT</th>
                <th class="h-12 px-4 text-center font-medium text-blue-600">Regu Piket</th>
                <th class="h-12 px-4 text-center font-medium text-emerald-600">Total Isi</th>
                <th class="h-12 px-4 text-center font-medium text-red-600">Overcrowded</th>
                <th class="h-12 px-4 text-right font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datapenghuniriils.data" :key="d.id" class="border-b hover:bg-muted/50">
                <td class="p-4 font-bold">{{ d.tanggal_input }}</td>
                <td class="p-4">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 text-center font-semibold text-blue-700">{{ d.nama_regu }}</td>
                <td class="p-4 text-center font-bold text-emerald-600">{{ d.total_isi }}</td>
                <td class="p-4 text-center font-bold text-red-600">{{ d.overcrowded }}%</td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Lihat</button>
                    <Link :href="`/admin/data-penghuni-riils/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
                    <button @click="destroyData(d.id)" class="px-3 py-1 bg-red-500 text-white rounded-md text-xs font-medium">Hapus</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @click.self="closeModal">
      <div class="w-full max-w-4xl rounded-xl bg-card shadow-lg flex flex-col max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center border-b p-5 bg-card">
            <h2 class="text-lg font-bold">Laporan Papan Portir: {{ selectedData?.upt?.name }} ({{ selectedData?.tanggal_input }})</h2>
            <button @click="closeModal" class="text-2xl leading-none">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto bg-muted/10 grid lg:grid-cols-3 gap-6">
            
            <div class="bg-card border rounded p-4">
                <h3 class="font-bold text-blue-700 border-b pb-2 mb-3">Pengamanan</h3>
                <p class="text-xs text-muted-foreground">Regu: <strong class="text-foreground text-sm">{{ selectedData?.nama_regu }}</strong></p>
                <div class="flex gap-4 mt-2">
                    <div><p class="text-xs">Hadir: <b>{{ selectedData?.jumlah_hadir }}</b></p></div>
                    <div><p class="text-xs text-red-600">Alpa: <b>{{ selectedData?.jumlah_tidak_hadir }}</b></p></div>
                </div>
                <p class="text-xs mt-2 border-t pt-2">Perwira Piket: <b>{{ selectedData?.perwira_piket || '-' }}</b></p>
                <p class="text-xs mt-1 border-t pt-2">Sambang: <b>{{ selectedData?.patroli_sambang || '-' }} ({{ selectedData?.waktu_patroli_sambang || '-' }})</b></p>
            </div>

            <div class="bg-card border rounded p-4">
                <h3 class="font-bold text-emerald-700 border-b pb-2 mb-3">Hunian</h3>
                <p class="text-xs">Kapasitas: <b>{{ selectedData?.jumlah_kapasitas }}</b></p>
                <p class="text-xs mt-1">Total Isi: <b class="text-emerald-600 text-sm">{{ selectedData?.total_isi }}</b></p>
                <p class="text-xs mt-1 border-b pb-2">Overcrowded: <b class="text-red-600">{{ selectedData?.overcrowded }}%</b></p>
                <div class="grid grid-cols-2 text-[10px] mt-2 gap-2">
                    <div>
                        <p class="font-bold">NAPI</p>
                        <p>L: {{ selectedData?.jumlah_napi_laki }}</p>
                        <p>P: {{ selectedData?.jumlah_napi_perempuan }}</p>
                        <p>A: {{ selectedData?.jumlah_napi_anak }}</p>
                    </div>
                    <div>
                        <p class="font-bold">TAHANAN</p>
                        <p>L: {{ selectedData?.jumlah_tahanan_laki }}</p>
                        <p>P: {{ selectedData?.jumlah_tahanan_perempuan }}</p>
                        <p>A: {{ selectedData?.jumlah_tahanan_anak }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-card border rounded p-4">
                <h3 class="font-bold text-orange-700 border-b pb-2 mb-3">Pengeluaran</h3>
                <ul class="space-y-1">
                    <li v-for="(val, key) in selectedData?.jumlah_pengeluaran" :key="key" class="flex justify-between text-xs border-b border-dashed pb-1">
                        <span class="text-muted-foreground">{{ findPengeluaranName(key) }}</span>
                        <span class="font-bold">{{ val }}</span>
                    </li>
                </ul>
            </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>