<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ databulanansdms: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

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
  if (!confirm('Hapus laporan bulanan SDM ini?')) return
  router.delete(`/admin/data-bulanan-sdms/${id}`)
}
</script>

<template>
  <Head title="Bulanan SDM" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight text-primary">Data Bulanan SDM (TU & Umum)</h1></div>
        <Link href="/admin/data-bulanan-sdms/create" class="inline-flex items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-bold shadow hover:bg-primary/90">+ Input Rekap Bulanan</Link>
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
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">Bulan Laporan</th>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">UPT</th>
                <th class="h-12 px-4 text-center font-bold text-emerald-700">Total Pegawai</th>
                <th class="h-12 px-4 text-center font-bold text-muted-foreground">Penjagaan / Pembinaan</th>
                <th class="h-12 px-4 text-right font-bold text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in databulanansdms.data" :key="d.id" class="border-b hover:bg-muted/30">
                <td class="p-4 font-bold">{{ d.tanggal_input }}</td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 text-center"><span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-xs font-black">{{ d.jumlah_pegawai }} Org</span></td>
                <td class="p-4 text-center font-medium">{{ d.jumlah_petugas_penjagaan }} / {{ d.jumlah_staff_pembinaan }}</td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Detail</button>
                    <Link :href="`/admin/data-bulanan-sdms/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
            <h2 class="text-lg font-black text-primary">Detail Formasi SDM: {{ selectedData?.upt?.name }}</h2>
            <button @click="closeModal" class="text-2xl leading-none text-muted-foreground hover:text-red-500">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            
            <div class="text-center pb-6 border-b border-border">
                <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-1">Total Pegawai Bulan: {{ selectedData?.tanggal_input }}</p>
                <p class="text-5xl font-black text-emerald-600">{{ selectedData?.jumlah_pegawai }}</p>
                <p class="text-xs mt-2 bg-emerald-50 text-emerald-800 px-3 py-1 rounded inline-block font-bold">Orang Pegawai</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl flex justify-between items-center">
                    <span class="text-xs font-bold text-blue-800 uppercase">Pejabat Struktural</span>
                    <span class="text-xl font-black text-blue-600">{{ selectedData?.jumlah_pejabat_struktural }}</span>
                </div>
                <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl flex justify-between items-center">
                    <span class="text-xs font-bold text-indigo-800 uppercase">Jabatan Fungsional</span>
                    <span class="text-xl font-black text-indigo-600">{{ selectedData?.jumlah_jf }}</span>
                </div>
                <div class="bg-orange-50 border border-orange-100 p-4 rounded-xl flex justify-between items-center">
                    <span class="text-xs font-bold text-orange-800 uppercase">Petugas Penjagaan</span>
                    <span class="text-xl font-black text-orange-600">{{ selectedData?.jumlah_petugas_penjagaan }}</span>
                </div>
                <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-xl flex justify-between items-center">
                    <span class="text-xs font-bold text-emerald-800 uppercase">Staf Pembinaan</span>
                    <span class="text-xl font-black text-emerald-600">{{ selectedData?.jumlah_staff_pembinaan }}</span>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl flex justify-between items-center">
                <span class="text-sm font-bold text-slate-700 uppercase">Staf Lainnya</span>
                <span class="text-2xl font-black text-slate-800">{{ selectedData?.jumlah_staff }}</span>
            </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>