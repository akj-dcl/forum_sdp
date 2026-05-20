<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ datahariansdms: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

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
  if (!confirm('Hapus laporan SDM ini?')) return
  router.delete(`/admin/data-harian-sdms/${id}`)
}
</script>

<template>
  <Head title="Harian SDM" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight text-primary">Data Harian SDM</h1></div>
        <Link href="/admin/data-harian-sdms/create" class="inline-flex items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-bold shadow hover:bg-primary/90">+ Lapor Presensi SDM</Link>
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
                <th class="h-12 px-4 text-center font-bold text-muted-foreground">Total Pegawai</th>
                <th class="h-12 px-4 text-center font-bold text-emerald-700">Hadir</th>
                <th class="h-12 px-4 text-center font-bold text-red-700">Pelanggaran</th>
                <th class="h-12 px-4 text-right font-bold text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datahariansdms.data" :key="d.id" class="border-b hover:bg-muted/30">
                <td class="p-4 font-bold">{{ d.tanggal_input }}</td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 text-center font-bold text-primary">{{ d.jumlah_pegawai_keseluruhan }} Org</td>
                <td class="p-4 text-center font-bold text-emerald-600 bg-emerald-50/50">{{ d.kehadiran_pegawai?.hadir || 0 }} Org</td>
                <td class="p-4 text-center">
                    <span v-if="d.pelanggaran_sdm && d.pelanggaran_sdm.toLowerCase() !== 'nihil'" class="bg-red-100 text-red-700 px-2 py-1 rounded text-[10px] font-bold uppercase">Ada Pelanggaran</span>
                    <span v-else class="text-muted-foreground text-xs italic">Nihil</span>
                </td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Detail</button>
                    <Link :href="`/admin/data-harian-sdms/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
            <h2 class="text-lg font-black text-primary">Detail Presensi SDM: {{ selectedData?.upt?.name }}</h2>
            <button @click="closeModal" class="text-2xl leading-none text-muted-foreground hover:text-red-500">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            
            <div class="text-center pb-4 border-b border-border">
                <p class="text-sm font-bold text-muted-foreground uppercase tracking-widest">Tanggal Laporan</p>
                <p class="text-xl font-black text-foreground">{{ selectedData?.tanggal_input }}</p>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 text-center">
                    <p class="text-xs font-bold text-blue-600 uppercase mb-2">Total Seluruh Pegawai</p>
                    <p class="text-4xl font-black text-blue-900">{{ selectedData?.jumlah_pegawai_keseluruhan }}</p>
                </div>
                
                <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 text-center">
                    <p class="text-xs font-bold text-emerald-600 uppercase mb-2">Hadir Bertugas</p>
                    <p class="text-4xl font-black text-emerald-700">{{ selectedData?.kehadiran_pegawai?.hadir || 0 }}</p>
                </div>
            </div>

            <div class="border rounded-xl p-4 bg-card shadow-sm">
                <h3 class="font-bold text-sm text-muted-foreground border-b pb-2 mb-3 uppercase">Rincian Tidak Hadir</h3>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-[10px] font-bold text-orange-600 uppercase bg-orange-100 py-1 rounded">Cuti</p>
                        <p class="text-xl font-bold mt-2">{{ selectedData?.kehadiran_pegawai?.cuti || 0 }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-yellow-600 uppercase bg-yellow-100 py-1 rounded">Sakit</p>
                        <p class="text-xl font-bold mt-2">{{ selectedData?.kehadiran_pegawai?.sakit || 0 }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-600 uppercase bg-slate-200 py-1 rounded">Izin</p>
                        <p class="text-xl font-bold mt-2">{{ selectedData?.kehadiran_pegawai?.ijin || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="border rounded-xl p-5" :class="(selectedData?.pelanggaran_sdm && selectedData?.pelanggaran_sdm.toLowerCase() !== 'nihil') ? 'bg-red-50/50 border-red-200' : 'bg-muted/30 border-border'">
                <h3 class="font-bold text-sm mb-2 uppercase" :class="(selectedData?.pelanggaran_sdm && selectedData?.pelanggaran_sdm.toLowerCase() !== 'nihil') ? 'text-red-700' : 'text-muted-foreground'">Catatan Pelanggaran SDM</h3>
                <p class="text-sm italic" :class="(selectedData?.pelanggaran_sdm && selectedData?.pelanggaran_sdm.toLowerCase() !== 'nihil') ? 'text-red-900 font-medium' : 'text-muted-foreground'">
                    {{ selectedData?.pelanggaran_sdm || 'Nihil' }}
                </p>
            </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>