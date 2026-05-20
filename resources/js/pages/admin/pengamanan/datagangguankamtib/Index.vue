<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ datagangguankamtibs: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

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
  if (!confirm('Hapus laporan insiden ini?')) return
  router.delete(`/admin/data-gangguan-kamtibs/${id}`)
}
</script>

<template>
  <Head title="Gangguan Kamtib" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight text-red-600">Laporan Gangguan Kamtib</h1></div>
        <Link href="/admin/data-gangguan-kamtibs/create" class="inline-flex items-center justify-center rounded-md bg-red-600 text-white px-4 py-2 text-sm font-bold shadow hover:bg-red-700">+ Lapor Insiden</Link>
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
            <thead class="bg-red-50/50">
              <tr class="border-b border-red-100">
                <th class="h-12 px-4 text-left font-bold text-red-800">Tgl Kejadian</th>
                <th class="h-12 px-4 text-left font-bold text-red-800">UPT</th>
                <th class="h-12 px-4 text-left font-bold text-red-800">Jenis Gangguan</th>
                <th class="h-12 px-4 text-center font-bold text-red-800">Korban / Barbuk</th>
                <th class="h-12 px-4 text-right font-bold text-red-800">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datagangguankamtibs.data" :key="d.id" class="border-b hover:bg-muted/50">
                <td class="p-4 font-bold">{{ d.tanggal_kejadian }} <span class="text-xs text-muted-foreground block">{{ d.waktu_kejadian }}</span></td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4"><span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">{{ d.jenis_gangguan }}</span></td>
                <td class="p-4 text-center"><span class="text-orange-600 font-bold">{{ d.jumlah_korban }}</span> / <span class="text-emerald-600 font-bold">{{ d.jumlah_barbuk }}</span></td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Detail</button>
                    <Link :href="`/admin/data-gangguan-kamtibs/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
        <div class="flex justify-between items-center border-b p-5 bg-red-50">
            <h2 class="text-lg font-black text-red-800">Insiden Kamtib: {{ selectedData?.upt?.name }}</h2>
            <button @click="closeModal" class="text-2xl leading-none text-red-800 hover:text-red-500">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            <div class="grid grid-cols-2 gap-4 border-b pb-4">
                <div><p class="text-xs text-muted-foreground">Tgl & Waktu Kejadian</p><p class="font-bold text-red-600">{{ selectedData?.tanggal_kejadian }} / {{ selectedData?.waktu_kejadian }}</p></div>
                <div><p class="text-xs text-muted-foreground">Jenis Gangguan</p><p class="font-bold bg-red-100 text-red-800 inline-block px-2 py-0.5 rounded mt-1">{{ selectedData?.jenis_gangguan }}</p></div>
                <div class="col-span-2"><p class="text-xs text-muted-foreground mb-1">Uraian Kejadian</p><p class="text-sm bg-muted/30 p-3 rounded border italic">"{{ selectedData?.uraian_singkat }}"</p></div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="border rounded p-3 bg-orange-50/50 border-orange-100">
                    <p class="text-xs font-bold text-orange-600 mb-2 border-b border-orange-200 pb-1">Daftar Korban ({{ selectedData?.jumlah_korban }} Org)</p>
                    <ul class="text-sm space-y-1 list-disc pl-4 text-orange-900">
                        <li v-for="(k, i) in selectedData?.detail_korban" :key="i">{{ k }}</li>
                        <li v-if="!selectedData?.detail_korban?.length" class="text-xs text-muted-foreground italic list-none">Tidak ada data / Nihil.</li>
                    </ul>
                </div>
                <div class="border rounded p-3 bg-emerald-50/50 border-emerald-100">
                    <p class="text-xs font-bold text-emerald-600 mb-2 border-b border-emerald-200 pb-1">Barang Bukti ({{ selectedData?.jumlah_barbuk }} Item)</p>
                    <ul class="text-sm space-y-1 list-disc pl-4 text-emerald-900">
                        <li v-for="(b, i) in selectedData?.detail_barbuk" :key="i">{{ b }}</li>
                        <li v-if="!selectedData?.detail_barbuk?.length" class="text-xs text-muted-foreground italic list-none">Tidak ada data / Nihil.</li>
                    </ul>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <p><b>Area Dirusak:</b> {{ selectedData?.area_dirusak || 'Nihil' }}</p>
                <p><b>Pengamanan TKP:</b> {{ selectedData?.pengamanan_tkp || 'Nihil' }}</p>
                <p><b>Upaya Dilakukan:</b> {{ selectedData?.upaya_dilakukan || 'Nihil' }}</p>
                <p><b>Bantuan:</b> {{ selectedData?.permohonan_bantuan || 'Nihil' }}</p>
            </div>

            <div class="bg-blue-50 p-4 rounded-lg flex justify-between items-center border border-blue-100">
                <div><h4 class="font-bold text-sm text-blue-800">Laporan Resmi BAP (PDF)</h4></div>
                <a :href="`/storage/${selectedData?.dokumentasi}`" target="_blank" class="bg-blue-600 text-white px-5 py-2 rounded-md text-sm font-bold shadow hover:bg-blue-700">Lihat PDF BAP</a>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>