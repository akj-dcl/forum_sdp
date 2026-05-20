<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ datainformasiintelejens: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

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
  if (!confirm('Hapus laporan informasi intelijen ini?')) return
  router.delete(`/admin/data-informasi-intelejens/${id}`)
}
</script>

<template>
  <Head title="Informasi Intelijen" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight text-primary">Data Informasi Intelijen</h1></div>
        <Link href="/admin/data-informasi-intelejens/create" class="inline-flex items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-bold shadow hover:bg-primary/90">+ Input Laporan Intelijen</Link>
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
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">Tgl Terima Info</th>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">UPT</th>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">Narasumber</th>
                <th class="h-12 px-4 text-left font-bold text-red-600">Potensi Gangguan</th>
                <th class="h-12 px-4 text-right font-bold text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datainformasiintelejens.data" :key="d.id" class="border-b hover:bg-muted/30">
                <td class="p-4 font-bold">{{ d.tanggal_pelaksanaan || d.tanggal_input }} <span class="text-xs font-normal text-muted-foreground block">{{ d.waktu_pelaksanaan || '-' }}</span></td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold">{{ d.narasumber || 'Tidak Disebutkan' }}</span></td>
                <td class="p-4 text-red-600 font-medium truncate max-w-[200px]">{{ d.potensi_gangguan || 'Nihil' }}</td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Detail</button>
                    <Link :href="`/admin/data-informasi-intelejens/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
      <div class="w-full max-w-4xl rounded-xl bg-card shadow-lg flex flex-col max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center border-b p-5 bg-muted/20">
            <h2 class="text-lg font-black">Detail Informasi Intelijen: {{ selectedData?.upt?.name }}</h2>
            <button @click="closeModal" class="text-2xl leading-none text-muted-foreground hover:text-red-500">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            
            <div class="grid md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4 bg-blue-50/50 shadow-sm border-blue-100">
                    <p class="text-[10px] font-bold text-blue-600 mb-1 uppercase">Waktu Terima & Narasumber</p>
                    <p class="text-sm mb-2"><span class="font-bold">{{ selectedData?.tanggal_pelaksanaan || selectedData?.tanggal_input }}</span> ({{ selectedData?.waktu_pelaksanaan || '-' }})</p>
                    <p class="text-sm font-bold bg-white px-2 py-1 rounded inline-block border border-blue-200">{{ selectedData?.narasumber || 'Narasumber Anonim' }}</p>
                </div>
                <div class="border rounded-lg p-4 bg-card shadow-sm">
                    <p class="text-[10px] font-bold text-muted-foreground mb-1 uppercase">Data Masuk</p>
                    <p class="text-sm italic text-foreground">"{{ selectedData?.data_yang_masuk || '-' }}"</p>
                </div>
            </div>

            <div class="border rounded-lg p-4 bg-card shadow-sm">
                <p class="text-[10px] font-bold text-muted-foreground mb-1 uppercase">Data Dukung</p>
                <p class="text-sm">{{ selectedData?.data_dukung || 'Tidak ada data dukung.' }}</p>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div class="border rounded-lg p-4 bg-red-50/50 border-red-100 shadow-sm">
                    <p class="text-[10px] font-bold text-red-600 mb-2 uppercase border-b border-red-200 pb-1">Potensi Gangguan Kamtib</p>
                    <p class="text-sm text-red-900">{{ selectedData?.potensi_gangguan || 'Nihil' }}</p>
                </div>
                <div class="border rounded-lg p-4 bg-emerald-50/50 border-emerald-100 shadow-sm">
                    <p class="text-[10px] font-bold text-emerald-600 mb-2 uppercase border-b border-emerald-200 pb-1">Rekomendasi Antisipasi</p>
                    <p class="text-sm text-emerald-900">{{ selectedData?.rekomendasi_antisipasi || 'Nihil' }}</p>
                </div>
                <div class="border rounded-lg p-4 bg-blue-50/50 border-blue-100 shadow-sm">
                    <p class="text-[10px] font-bold text-blue-600 mb-2 uppercase border-b border-blue-200 pb-1">Tindak Lanjut</p>
                    <p class="text-sm text-blue-900">{{ selectedData?.tindak_lanjut_rekomendasi || 'Belum ada tindak lanjut.' }}</p>
                </div>
            </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>