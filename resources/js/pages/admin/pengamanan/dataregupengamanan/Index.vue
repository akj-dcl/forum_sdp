<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ dataregupengamanans: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

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
  if (!confirm('Hapus laporan regu pengamanan ini?')) return
  router.delete(`/admin/data-regu-pengamanans/${id}`)
}
</script>

<template>
  <Head title="Regu Pengamanan" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-semibold tracking-tight">Laporan Regu Pengamanan (RUPAM)</h1></div>
        <Link href="/admin/data-regu-pengamanans/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90">+ Input Laporan Shift</Link>
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
                <th class="h-12 px-4 text-left font-medium">Shift / Waktu</th>
                <th class="h-12 px-4 text-left font-medium">UPT</th>
                <th class="h-12 px-4 text-left font-medium text-primary">Regu & Komandan</th>
                <th class="h-12 px-4 text-center font-medium">Kehadiran</th>
                <th class="h-12 px-4 text-right font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in dataregupengamanans.data" :key="d.id" class="border-b hover:bg-muted/50">
                <td class="p-4 font-bold">{{ d.tanggal_input }}</td>
                <td class="p-4"><span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs font-bold">{{ d.shift_rupam }}</span> <div class="text-xs text-muted-foreground mt-1">{{ d.waktu_tugas_mulai }} - {{ d.waktu_tugas_selesai }}</div></td>
                <td class="p-4 text-muted-foreground">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4"><div class="font-bold text-primary">{{ d.nama_regu }}</div><div class="text-xs text-muted-foreground">Koor: {{ d.nama_komandan_jaga || '-' }}</div></td>
                <td class="p-4 text-center"><span class="text-emerald-600 font-bold">{{ d.jumlah_hadir }}</span> / {{ d.jumlah_regu_jaga }} Org</td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Lihat Detail</button>
                    <Link :href="`/admin/data-regu-pengamanans/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
            <h2 class="text-lg font-bold">Laporan Shift RUPAM: {{ selectedData?.upt?.name }}</h2>
            <button @click="closeModal" class="text-2xl leading-none">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            <div class="flex justify-between items-center bg-blue-50/50 p-4 rounded-lg border border-blue-100">
                <div>
                    <p class="text-xs text-blue-600 font-bold uppercase mb-1">Tgl & Waktu Shift</p>
                    <p class="text-lg font-black text-blue-900">{{ selectedData?.tanggal_input }} <span class="text-base font-medium">({{ selectedData?.waktu_tugas_mulai }} s/d {{ selectedData?.waktu_tugas_selesai }})</span></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-blue-600 font-bold uppercase mb-1">Nama Regu</p>
                    <p class="text-lg font-black text-blue-900">{{ selectedData?.nama_regu }} <span class="text-sm font-normal bg-blue-200 px-2 py-0.5 rounded">{{ selectedData?.shift_rupam }}</span></p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="border rounded-lg p-4 bg-card">
                    <h3 class="font-bold text-primary mb-3 border-b pb-2">Kehadiran & Susunan Petugas</h3>
                    <div class="flex justify-between items-center mb-3 bg-muted/30 p-2 rounded">
                        <span class="text-xs font-bold text-emerald-600">Hadir: {{ selectedData?.jumlah_hadir }}</span>
                        <span class="text-xs font-bold text-red-600">Tidak Hadir: {{ selectedData?.jumlah_tidak_hadir }}</span>
                    </div>
                    <ul class="space-y-2 text-sm">
                        <li class="flex justify-between"><span class="text-muted-foreground">Komandan:</span> <span class="font-semibold">{{ selectedData?.nama_komandan_jaga || '-' }}</span></li>
                        <li class="flex justify-between"><span class="text-muted-foreground">Wadan:</span> <span class="font-semibold">{{ selectedData?.nama_wadan_jaga || '-' }}</span></li>
                        <li class="flex justify-between"><span class="text-muted-foreground">P2U:</span> <span class="font-semibold">{{ selectedData?.nama_p2u || '-' }}</span></li>
                        <li class="flex justify-between"><span class="text-muted-foreground">Petugas Luar:</span> <span class="font-semibold">{{ selectedData?.jumlah_petugas_luar }} Org</span></li>
                        <li v-if="selectedData?.jenis_bantuan" class="flex justify-between border-t pt-2 mt-2"><span class="text-orange-600 font-bold">Bantuan {{ selectedData?.jenis_bantuan }}:</span> <span class="font-bold text-orange-600">{{ selectedData?.jumlah_bantuan }} Org</span></li>
                    </ul>
                </div>
                
                <div class="border rounded-lg p-4 bg-card flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-emerald-600 mb-3 border-b pb-2">Status Titik Pos ({{ selectedData?.jumlah_titik_pos }} Pos)</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-[10px] font-bold text-emerald-600 uppercase">Diisi:</p>
                                <p class="text-sm bg-emerald-50 p-2 rounded border border-emerald-100">{{ selectedData?.pos_pengamanan_diisi || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-red-500 uppercase">Tidak Diisi:</p>
                                <p class="text-sm bg-red-50 p-2 rounded border border-red-100">{{ selectedData?.pos_pengamanan_tidak_diisi || '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t text-center">
                        <a :href="`/storage/${selectedData?.dokumentasi}`" target="_blank" class="inline-block bg-primary text-primary-foreground px-6 py-2 rounded-md text-sm font-bold shadow hover:bg-primary/90">📄 Lihat PDF Laporan</a>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>