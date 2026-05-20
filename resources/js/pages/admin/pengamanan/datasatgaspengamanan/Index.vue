<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ datasatgaspengamanans: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

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
  if (!confirm('Hapus laporan satgas ini?')) return
  router.delete(`/admin/data-satgas-pengamanans/${id}`)
}
</script>

<template>
  <Head title="Satgas Pengamanan" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight text-primary">Laporan Satgas Pengamanan</h1></div>
        <Link href="/admin/data-satgas-pengamanans/create" class="inline-flex items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-bold shadow">+ Lapor Satgas</Link>
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
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">UPT</th>
                <th class="h-12 px-4 text-left font-bold text-blue-700">Pelaksanaan Razia</th>
                <th class="h-12 px-4 text-left font-bold text-emerald-700">Pelaksanaan Narkoba</th>
                <th class="h-12 px-4 text-right font-bold text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datasatgaspengamanans.data" :key="d.id" class="border-b hover:bg-muted/30">
                <td class="p-4 font-bold">{{ d.tanggal_input }}</td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4">
                    <span v-if="d.tanggal_pelaksanaan_penggeledahan" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded font-bold">{{ d.tanggal_pelaksanaan_penggeledahan }}</span>
                    <span v-else class="text-xs text-muted-foreground italic">Nihil</span>
                </td>
                <td class="p-4">
                    <span v-if="d.tanggal_pelaksanaan_narkoba" class="text-xs bg-emerald-100 text-emerald-800 px-2 py-1 rounded font-bold">{{ d.tanggal_pelaksanaan_narkoba }}</span>
                    <span v-else class="text-xs text-muted-foreground italic">Nihil</span>
                </td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Detail</button>
                    <Link :href="`/admin/data-satgas-pengamanans/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
            <h2 class="text-lg font-black">Detail Laporan Satgas: {{ selectedData?.upt?.name }}</h2>
            <button @click="closeModal" class="text-2xl leading-none text-muted-foreground hover:text-red-500">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto grid md:grid-cols-2 gap-6">
            
            <div class="border rounded-lg p-4 bg-blue-50/20 shadow-sm border-blue-100">
                <p class="text-sm text-blue-800 border-b pb-2 mb-3 font-bold">RAZIA KAMAR</p>
                <div v-if="selectedData?.tanggal_pelaksanaan_penggeledahan" class="space-y-3">
                    <div class="flex justify-between text-sm"><span>Waktu:</span> <span class="font-bold">{{ selectedData.tanggal_pelaksanaan_penggeledahan }} ({{ selectedData.waktu_pelaksanaan_penggeledahan }})</span></div>
                    <div class="flex justify-between text-sm"><span>Lokasi:</span> <span class="font-bold">{{ selectedData.lokasi_kamar_penggeledahan }}</span></div>
                    <div class="flex justify-between text-sm"><span>Anggota:</span> <span class="font-bold">{{ selectedData.jumlah_anggota_penggeledahan }} Org</span></div>
                    <div class="bg-white p-2 rounded border text-sm mt-2"><p class="text-xs font-bold text-muted-foreground mb-1">Hasil:</p>{{ selectedData.hasil_penggeledahan || '-' }}</div>
                    <div class="bg-blue-50 p-2 rounded border border-blue-200 text-sm"><p class="text-xs font-bold text-blue-700 mb-1">Tindak Lanjut:</p>{{ selectedData.tindak_lanjut_penggeledahan || '-' }}</div>
                </div>
                <div v-else class="text-center text-muted-foreground italic py-4">Tidak ada laporan razia hari ini.</div>
            </div>

            <div class="border rounded-lg p-4 bg-emerald-50/20 shadow-sm border-emerald-100">
                <p class="text-sm text-emerald-800 border-b pb-2 mb-3 font-bold">TES NARKOBA</p>
                <div v-if="selectedData?.tanggal_pelaksanaan_narkoba" class="space-y-3">
                    <div class="flex justify-between text-sm"><span>Waktu:</span> <span class="font-bold">{{ selectedData.tanggal_pelaksanaan_narkoba }} ({{ selectedData.waktu_pelaksanaan_narkoba }})</span></div>
                    <div class="flex justify-between text-sm"><span>Jml Dites:</span> <span class="font-bold">{{ selectedData.jumlah_yang_dites_narkoba }} Org</span></div>
                    <div class="bg-white p-2 rounded border text-sm mt-2"><p class="text-xs font-bold text-muted-foreground mb-1">Hasil:</p>{{ selectedData.hasil_tes_narkoba || '-' }}</div>
                    <div class="bg-emerald-50 p-2 rounded border border-emerald-200 text-sm"><p class="text-xs font-bold text-emerald-700 mb-1">Tindak Lanjut:</p>{{ selectedData.tindak_lanjut_hasil_tes || '-' }}</div>
                </div>
                <div v-else class="text-center text-muted-foreground italic py-4">Tidak ada laporan tes narkoba hari ini.</div>
            </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>