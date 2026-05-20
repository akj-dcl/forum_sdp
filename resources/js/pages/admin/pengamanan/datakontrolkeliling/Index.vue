<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ datakontrolkelilings: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

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
  if (!confirm('Hapus data kontrol keliling ini?')) return
  router.delete(`/admin/data-kontrol-kelilings/${id}`)
}
</script>

<template>
  <Head title="Data Kontrol Keliling" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-semibold tracking-tight">Laporan Kontrol Keliling</h1></div>
        <Link href="/admin/data-kontrol-kelilings/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow">+ Input Laporan Baru</Link>
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
                <th class="h-12 px-4 text-left font-medium">Waktu</th>
                <th class="h-12 px-4 text-left font-medium">UPT</th>
                <th class="h-12 px-4 text-left font-medium text-primary">Petugas Kontrol</th>
                <th class="h-12 px-4 text-left font-medium">Area</th>
                <th class="h-12 px-4 text-right font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datakontrolkelilings.data" :key="d.id" class="border-b hover:bg-muted/50">
                <td class="p-4 font-bold">{{ d.tanggal_input }}</td>
                <td class="p-4 text-muted-foreground">{{ d.waktu_kontrol || '-' }}</td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 text-primary font-bold">{{ d.nama_petugas_kontrol }}</td>
                <td class="p-4 text-muted-foreground">{{ d.area_kontrol || '-' }}</td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Lihat</button>
                    <Link :href="`/admin/data-kontrol-kelilings/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
      <div class="w-full max-w-3xl rounded-xl bg-card shadow-lg flex flex-col max-h-[90vh] overflow-hidden">
        <div class="flex justify-between items-center border-b p-5 bg-card">
            <h2 class="text-lg font-bold">Detail Kontrol: {{ selectedData?.upt?.name }}</h2>
            <button @click="closeModal" class="text-2xl leading-none">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            <div class="grid grid-cols-2 gap-4 border-b pb-4">
                <div><p class="text-xs text-muted-foreground">Tgl & Waktu</p><p class="font-bold">{{ selectedData?.tanggal_input }} / {{ selectedData?.waktu_kontrol || '-' }}</p></div>
                <div><p class="text-xs text-muted-foreground">Nama Petugas</p><p class="font-bold text-primary">{{ selectedData?.nama_petugas_kontrol }}</p></div>
                <div><p class="text-xs text-muted-foreground">Area Kontrol</p><p class="font-medium">{{ selectedData?.area_kontrol || '-' }}</p></div>
                <div><p class="text-xs text-muted-foreground">Hasil Kontrol</p><p class="font-medium">{{ selectedData?.hasil_kontrol || '-' }}</p></div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-orange-50 p-3 rounded-lg border border-orange-100">
                    <p class="text-[10px] font-bold text-orange-600 uppercase mb-1">Tindak Lanjut</p>
                    <p class="text-sm">{{ selectedData?.tindak_lanjut || 'Tidak ada catatan.' }}</p>
                </div>
                <div class="bg-emerald-50 p-3 rounded-lg border border-emerald-100">
                    <p class="text-[10px] font-bold text-emerald-600 uppercase mb-1">Rekomendasi</p>
                    <p class="text-sm">{{ selectedData?.rekomendasi || 'Tidak ada rekomendasi.' }}</p>
                </div>
            </div>

            <div class="bg-blue-50 p-4 rounded-lg flex justify-between items-center border border-blue-100">
                <div>
                    <h4 class="font-bold text-sm text-blue-800">Dokumen Laporan PDF</h4>
                </div>
                <a :href="`/storage/${selectedData?.dokumentasi_kontrol}`" target="_blank" class="bg-blue-600 text-white px-5 py-2 rounded-md text-sm font-bold hover:bg-blue-700 shadow">Lihat Dokumen</a>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>