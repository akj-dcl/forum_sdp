<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ dataperawatanbulanans: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

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
  if (!confirm('Hapus data perawatan bulanan ini?')) return
  router.delete(`/admin/data-perawatan-bulanans/${id}`)
}
</script>

<template>
  <Head title="Data Perawatan Bulanan" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-semibold tracking-tight">Data Perawatan Bulanan</h1></div>
        <Link href="/admin/data-perawatan-bulanans/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">+ Input Laporan Bulanan</Link>
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
                <th class="h-12 px-4 text-left font-medium">Bulan Laporan</th>
                <th class="h-12 px-4 text-left font-medium">UPT</th>
                <th class="h-12 px-4 text-center font-medium">Penyakit Dominan</th>
                <th class="h-12 px-4 text-center font-medium text-blue-600">Peserta BPJS</th>
                <th class="h-12 px-4 text-right font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in dataperawatanbulanans.data" :key="d.id" class="border-b hover:bg-muted/50">
                <td class="p-4 font-bold">{{ d.tanggal_input }}</td>
                <td class="p-4">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 text-center"><span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">{{ d.jenis_penyakit_dominan }}</span></td>
                <td class="p-4 text-center font-bold text-blue-600">{{ d.jumlah_peserta_bpjs }}</td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Lihat Detail</button>
                    <Link :href="`/admin/data-perawatan-bulanans/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
            <h2 class="text-lg font-bold">Laporan Perawatan Bulanan: {{ selectedData?.upt?.name }} ({{ selectedData?.tanggal_input }})</h2>
            <button @click="closeModal" class="text-2xl leading-none hover:text-red-500">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto bg-muted/10">
            <div class="grid md:grid-cols-2 gap-6">
                
                <div class="bg-card border rounded-lg p-4">
                    <h3 class="text-sm font-bold text-red-600 border-b pb-2 mb-3">Penyakit Menular & Khusus</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex justify-between"><span class="text-muted-foreground">HIV</span> <span class="font-bold">{{ selectedData?.jumlah_penghuni_hiv }}</span></li>
                        <li class="flex justify-between"><span class="text-muted-foreground">TBC</span> <span class="font-bold">{{ selectedData?.jumlah_penghuni_tbc }}</span></li>
                        <li class="flex justify-between"><span class="text-muted-foreground">Gangguan Jiwa</span> <span class="font-bold">{{ selectedData?.jumlah_penghuni_jiwa }}</span></li>
                        <li class="flex justify-between"><span class="text-muted-foreground">Menular Lainnya</span> <span class="font-bold">{{ selectedData?.jumlah_penghuni_menular }}</span></li>
                    </ul>
                    <div class="mt-4 pt-3 border-t">
                        <p class="text-xs text-muted-foreground mb-1">Penyakit Dominan Bulan Ini:</p>
                        <p class="font-bold text-red-700 bg-red-50 p-2 rounded text-center uppercase">{{ selectedData?.jenis_penyakit_dominan }}</p>
                    </div>
                </div>

                <div class="bg-card border rounded-lg p-4">
                    <h3 class="text-sm font-bold text-emerald-600 border-b pb-2 mb-3">Kelompok Rentan & Umum</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex justify-between"><span class="text-muted-foreground">Disabilitas</span> <span class="font-bold">{{ selectedData?.jumlah_penghuni_disabilitas }}</span></li>
                        <li class="flex justify-between"><span class="text-muted-foreground">Lansia (>60 Tahun)</span> <span class="font-bold">{{ selectedData?.jumlah_wbp_lansia }}</span></li>
                        <li class="flex justify-between"><span class="text-muted-foreground">Sakit Berkepanjangan</span> <span class="font-bold">{{ selectedData?.jumlah_wbp_berkepanjangan }}</span></li>
                        <li class="flex justify-between"><span class="text-muted-foreground">Penyakit Tidak Menular</span> <span class="font-bold">{{ selectedData?.jumlah_penghuni_tidakmenular }}</span></li>
                    </ul>
                    <div class="mt-4 pt-3 border-t">
                        <p class="text-xs text-muted-foreground mb-1">Kepemilikan BPJS Kesehatan:</p>
                        <p class="font-bold text-blue-700 bg-blue-50 p-2 rounded text-center">{{ selectedData?.jumlah_peserta_bpjs }} Orang</p>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 p-4 rounded-lg flex justify-between items-center border border-blue-100">
                <div>
                    <h4 class="font-bold text-sm text-blue-800">Dokumen Laporan Resmi</h4>
                    <p class="text-xs text-blue-600">Unduh PDF laporan bulanan yang ditandatangani.</p>
                </div>
                <a :href="`/storage/${selectedData?.laporan}`" target="_blank" class="bg-blue-600 text-white px-5 py-2 rounded-md text-sm font-bold hover:bg-blue-700 shadow">Lihat / Download PDF</a>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>