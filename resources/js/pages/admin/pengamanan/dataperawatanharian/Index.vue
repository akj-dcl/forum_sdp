<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ dataperawatanharians: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

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
  if (!confirm('Hapus data perawatan harian ini?')) return
  router.delete(`/admin/data-perawatan-harians/${id}`)
}
</script>

<template>
  <Head title="Data Perawatan Harian" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Data Perawatan Harian</h1>
        </div>
        <Link href="/admin/data-perawatan-harians/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">+ Input Perawatan</Link>
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
                <th class="h-12 px-4 text-center font-medium">Total Berobat/Dirawat</th>
                <th class="h-12 px-4 text-center font-medium text-red-600">Meninggal</th>
                <th class="h-12 px-4 text-right font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in dataperawatanharians.data" :key="d.id" class="border-b hover:bg-muted/50">
                <td class="p-4 font-bold">{{ d.tanggal_input }}</td>
                <td class="p-4">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 text-center font-bold text-primary">{{ d.jumlah_penghuni_berobat + d.jumlah_penghuni_dirawat + d.jumlah_nama_berobatjalan + d.jumlah_nama_rawatklinik }} Orang</td>
                <td class="p-4 text-center text-red-600 font-bold">{{ d.jumlah_wbp_meninggal }}</td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Lihat Detail</button>
                    <Link :href="`/admin/data-perawatan-harians/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
            <h2 class="text-lg font-bold">Laporan Perawatan: {{ selectedData?.upt?.name }} ({{ selectedData?.tanggal_input }})</h2>
            <button @click="closeModal" class="text-2xl leading-none">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="bg-blue-50/50 p-4 rounded-lg border border-blue-100">
                    <p class="text-xs font-bold text-blue-600 uppercase mb-1">Penghuni Berobat</p>
                    <p class="text-2xl font-black text-blue-700">{{ selectedData?.jumlah_penghuni_berobat }}</p>
                </div>
                <div class="bg-emerald-50/50 p-4 rounded-lg border border-emerald-100">
                    <p class="text-xs font-bold text-emerald-600 uppercase mb-1">Penghuni Dirawat</p>
                    <p class="text-2xl font-black text-emerald-700">{{ selectedData?.jumlah_penghuni_dirawat }}</p>
                </div>
                <div class="bg-red-50/50 p-4 rounded-lg border border-red-100">
                    <p class="text-xs font-bold text-red-600 uppercase mb-1">Meninggal Dunia</p>
                    <p class="text-2xl font-black text-red-700">{{ selectedData?.jumlah_wbp_meninggal }}</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="border rounded-lg overflow-hidden">
                    <div class="bg-orange-100 px-4 py-2 border-b font-bold text-orange-800 text-sm">
                        Berobat Jalan ({{ selectedData?.jumlah_nama_berobatjalan }} Orang)
                    </div>
                    <ul class="p-4 space-y-2 max-h-48 overflow-y-auto">
                        <li v-for="(nama, idx) in selectedData?.daftar_nama_berobatjalan" :key="idx" class="text-sm flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-orange-200 text-orange-800 flex items-center justify-center text-[10px] font-bold">{{ idx + 1 }}</span>
                            {{ nama }}
                        </li>
                        <li v-if="!selectedData?.daftar_nama_berobatjalan?.length" class="text-sm text-muted-foreground italic">Tidak ada data nama.</li>
                    </ul>
                </div>
                <div class="border rounded-lg overflow-hidden">
                    <div class="bg-emerald-100 px-4 py-2 border-b font-bold text-emerald-800 text-sm">
                        Rawat Klinik ({{ selectedData?.jumlah_nama_rawatklinik }} Orang)
                    </div>
                    <ul class="p-4 space-y-2 max-h-48 overflow-y-auto">
                        <li v-for="(nama, idx) in selectedData?.daftar_nama_rawatklinik" :key="idx" class="text-sm flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-emerald-200 text-emerald-800 flex items-center justify-center text-[10px] font-bold">{{ idx + 1 }}</span>
                            {{ nama }}
                        </li>
                        <li v-if="!selectedData?.daftar_nama_rawatklinik?.length" class="text-sm text-muted-foreground italic">Tidak ada data nama.</li>
                    </ul>
                </div>
            </div>

            <div class="bg-muted/50 p-4 rounded-lg flex justify-between items-center border">
                <div>
                    <h4 class="font-bold text-sm">Dokumen Laporan Resmi</h4>
                    <p class="text-xs text-muted-foreground">Unduh atau lihat PDF laporan yang ditandatangani.</p>
                </div>
                <a :href="`/storage/${selectedData?.dokumen}`" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-bold hover:bg-blue-700">Lihat PDF</a>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>