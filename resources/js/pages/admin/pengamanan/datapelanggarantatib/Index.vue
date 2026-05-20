<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ datapelanggarantatibs: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

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
  if (!confirm('Hapus laporan pelanggaran tatib ini?')) return
  router.delete(`/admin/data-pelanggaran-tatibs/${id}`)
}
</script>

<template>
  <Head title="Pelanggaran Tatib" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight text-red-600">Data Pelanggaran Tatib</h1></div>
        <Link href="/admin/data-pelanggaran-tatibs/create" class="inline-flex items-center justify-center rounded-md bg-red-600 text-white px-4 py-2 text-sm font-bold shadow hover:bg-red-700">+ Input Pelanggaran Baru</Link>
      </div>

      <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
        <input v-model="filterTanggal" type="date" class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm cursor-pointer" />
        <select v-model="filterUpt" class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm cursor-pointer">
          <option value="">Semua UPT</option>
          <option v-for="upt in upts" :key="upt.id" :value="String(upt.id)">{{ upt.name }}</option>
        </select>
      </div>

      <div class="rounded-xl border border-red-100 bg-card text-card-foreground shadow-sm overflow-hidden">
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm">
            <thead class="bg-red-50/50 border-b border-red-100">
              <tr>
                <th class="h-12 px-4 text-left font-bold text-red-800">Tgl Kejadian</th>
                <th class="h-12 px-4 text-left font-bold text-red-800">UPT</th>
                <th class="h-12 px-4 text-left font-bold text-red-800">Nama WBP</th>
                <th class="h-12 px-4 text-left font-bold text-red-800">Jenis Pelanggaran</th>
                <th class="h-12 px-4 text-right font-bold text-red-800">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datapelanggarantatibs.data" :key="d.id" class="border-b hover:bg-muted/30">
                <td class="p-4 font-bold">{{ d.tanggal_pelanggaran || d.tanggal_input }} <span class="text-xs font-normal text-muted-foreground block">{{ d.waktu_pelanggaran || '-' }}</span></td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4"><span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">{{ d.nama_wbp }}</span></td>
                <td class="p-4 text-muted-foreground font-medium truncate max-w-[200px]">{{ d.jenis_pelanggaran || '-' }}</td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Detail</button>
                    <Link :href="`/admin/data-pelanggaran-tatibs/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
      <div class="w-full max-w-3xl rounded-xl bg-card shadow-lg flex flex-col max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center border-b p-5 bg-red-50/80">
            <h2 class="text-lg font-black text-red-800">Detail Pelanggaran Tatib: {{ selectedData?.upt?.name }}</h2>
            <button @click="closeModal" class="text-2xl leading-none text-red-800 hover:text-red-500">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            
            <div class="grid md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4 bg-white shadow-sm border-border">
                    <p class="text-[10px] font-bold text-muted-foreground mb-1 uppercase">Identitas & Waktu</p>
                    <p class="text-lg font-black text-primary mb-1">{{ selectedData?.nama_wbp }}</p>
                    <p class="text-sm font-medium text-muted-foreground border-t pt-2 mt-2">Waktu Kejadian: <span class="text-foreground">{{ selectedData?.tanggal_pelanggaran || selectedData?.tanggal_input }} ({{ selectedData?.waktu_pelanggaran || '-' }})</span></p>
                </div>
                <div class="border rounded-lg p-4 bg-red-50/50 shadow-sm border-red-100">
                    <p class="text-[10px] font-bold text-red-600 mb-1 uppercase">Tindak Pelanggaran</p>
                    <p class="text-sm font-bold text-red-900 mb-2">{{ selectedData?.jenis_pelanggaran || '-' }}</p>
                    <p class="text-xs text-muted-foreground border-t border-red-200 pt-2">Pasal: <span class="font-semibold text-red-700">{{ selectedData?.pasal_pelanggaran || 'Tidak dicantumkan' }}</span></p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4 bg-blue-50/50 border-blue-100 shadow-sm">
                    <p class="text-[10px] font-bold text-blue-600 mb-2 uppercase border-b border-blue-200 pb-1">Tindak Lanjut Pelanggaran</p>
                    <p class="text-sm text-blue-900">{{ selectedData?.tindak_lanjut || 'Belum ada tindakan.' }}</p>
                </div>
                <div class="border rounded-lg p-4 bg-emerald-50/50 border-emerald-100 shadow-sm">
                    <p class="text-[10px] font-bold text-emerald-600 mb-2 uppercase border-b border-emerald-200 pb-1">Rekomendasi Pembinaan</p>
                    <p class="text-sm text-emerald-900">{{ selectedData?.rekomendasi_pembinaan || 'Tidak ada rekomendasi.' }}</p>
                </div>
            </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>