<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ 
    datasdmbulanans: any, upts: any[], 
    jenis_pendidikans: any[], jenis_usulans: any[], jenis_strukturals: any[], 
    jenis_golongans: any[], jenis_bebans: any[], jenis_staffs: any[],
    filters: { tanggal?: string, upt_id?: string } 
}>()

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
  router.delete(`/admin/data-sdm-bulanans/${id}`)
}

// Fungsi Pencari Nama Master
function findName(arr: any[], id: string, keyName: string) {
    const item = arr.find(i => String(i.id) === String(id));
    return item ? item[keyName] : '-';
}
</script>

<template>
  <Head title="SDM Bulanan Rinci" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight text-primary">Data SDM Bulanan (Rinci)</h1></div>
        <Link href="/admin/data-sdm-bulanans/create" class="inline-flex items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-bold shadow hover:bg-primary/90">+ Input Laporan SDM</Link>
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
                <th class="h-12 px-4 text-center font-bold text-blue-700">Laki-laki / Perempuan</th>
                <th class="h-12 px-4 text-right font-bold text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datasdmbulanans.data" :key="d.id" class="border-b hover:bg-muted/30">
                <td class="p-4 font-bold">{{ d.tanggal_input }}</td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 text-center font-bold">L: {{ d.jumlah_pegawai_laki }} | P: {{ d.jumlah_pegawai_perempuan }}</td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Buka Rekap</button>
                    <Link :href="`/admin/data-sdm-bulanans/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
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
      <div class="w-full max-w-5xl rounded-xl bg-card shadow-xl flex flex-col max-h-[90vh] overflow-hidden border border-border">
        
        <div class="flex justify-between items-center border-b border-border p-5 bg-muted/20">
            <h2 class="text-lg font-black text-primary">Rekapitulasi SDM Bulanan: {{ selectedData?.upt?.name }}</h2>
            <button @click="closeModal" class="text-2xl leading-none text-muted-foreground hover:text-red-500">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            
            <div class="grid md:grid-cols-3 gap-6">
                <div class="space-y-4">
                    <div class="border rounded p-3 bg-blue-50/30">
                        <p class="text-[10px] font-bold text-blue-700 mb-2 uppercase border-b pb-1">1. Pendidikan</p>
                        <div v-for="(val, key) in selectedData?.jumlah_pegawai_bypendidikan" :key="key" class="flex justify-between text-xs py-0.5"><span class="text-muted-foreground">{{ findName(jenis_pendidikans, key, 'nama_pendidikan') }}</span><span class="font-bold">{{ val }}</span></div>
                    </div>
                    <div class="border rounded p-3 bg-indigo-50/30">
                        <p class="text-[10px] font-bold text-indigo-700 mb-2 uppercase border-b pb-1">4. Golongan</p>
                        <div v-for="(val, key) in selectedData?.jumlah_pegawai_bygolongan" :key="key" class="flex justify-between text-xs py-0.5"><span class="text-muted-foreground">{{ findName(jenis_golongans, key, 'nama_golongan') }}</span><span class="font-bold">{{ val }}</span></div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="border rounded p-3 bg-orange-50/30">
                        <p class="text-[10px] font-bold text-orange-700 mb-2 uppercase border-b pb-1">3. Struktural</p>
                        <div v-for="(val, key) in selectedData?.jumlah_pejabat_struktural" :key="key" class="flex justify-between text-xs py-0.5"><span class="text-muted-foreground">{{ findName(jenis_strukturals, key, 'nama_struktural') }}</span><span class="font-bold">{{ val }}</span></div>
                    </div>
                    <div class="border rounded p-3 bg-emerald-50/30">
                        <p class="text-[10px] font-bold text-emerald-700 mb-2 uppercase border-b pb-1">2. Usulan & 6. Beban Kerja</p>
                        <div v-for="(val, key) in selectedData?.jumlah_pegawai_byusulan" :key="'u'+key" class="flex justify-between text-xs py-0.5"><span class="text-muted-foreground">{{ findName(jenis_usulans, key, 'nama_usulan') }}</span><span class="font-bold">{{ val }}</span></div>
                        <div class="border-t my-2 border-emerald-200"></div>
                        <div v-for="(val, key) in selectedData?.jumlah_pegawai_bybeban" :key="'b'+key" class="flex justify-between text-xs py-0.5"><span class="text-muted-foreground">{{ findName(jenis_bebans, key, 'nama_beban') }}</span><span class="font-bold">{{ val }}</span></div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="border rounded p-3 bg-slate-50">
                        <p class="text-[10px] font-bold text-slate-700 mb-2 uppercase border-b pb-1">Staf & Bidang</p>
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between"><span class="text-muted-foreground">Jabatan Fungsional</span><span class="font-bold">{{ selectedData?.jumlah_jf }}</span></div>
                            <div class="flex justify-between"><span class="text-muted-foreground">Staf Pembinaan</span><span class="font-bold">{{ selectedData?.jumlah_staff_pembinaan }}</span></div>
                            <div class="flex justify-between"><span class="text-muted-foreground">Staf Pengamanan</span><span class="font-bold">{{ selectedData?.jumlah_staff_pengamanan }}</span></div>
                            <div class="flex justify-between"><span class="text-muted-foreground">Staf TU & Umum</span><span class="font-bold">{{ selectedData?.jumlah_staff_tudanumum }}</span></div>
                            <div class="flex justify-between"><span class="text-muted-foreground">Staf Bimker</span><span class="font-bold">{{ selectedData?.jumlah_staff_bimker }}</span></div>
                        </div>
                    </div>
                    <div class="border rounded p-3 bg-red-50/30">
                        <p class="text-[10px] font-bold text-red-700 mb-2 uppercase border-b border-red-200 pb-1">Diklat & Hukuman</p>
                        <div class="flex justify-between text-xs py-0.5"><span class="text-muted-foreground">Diklat Teknis</span><span class="font-bold">{{ selectedData?.jumlah_pegawai_diktek }}</span></div>
                        <div class="flex justify-between text-xs py-0.5"><span class="text-muted-foreground">Diklat Manajerial</span><span class="font-bold">{{ selectedData?.jumlah_pegawai_dikman }}</span></div>
                        <div class="flex justify-between text-xs py-0.5"><span class="text-red-600 font-bold">Hukuman Disiplin</span><span class="font-bold text-red-700">{{ selectedData?.jumlah_pegawai_hukdis }}</span></div>
                    </div>
                </div>
            </div>

            <div class="border rounded p-4 bg-muted/10">
                <p class="text-[10px] font-bold text-primary mb-2 uppercase border-b pb-1">8. Detail Petugas Penjagaan</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div v-for="(val, key) in selectedData?.jumlah_petugas_penjagaan" :key="key" class="bg-white p-2 rounded border shadow-sm flex flex-col items-center">
                        <span class="text-xs text-muted-foreground text-center mb-1">{{ findName(jenis_staffs, key, 'nama_staff') }}</span>
                        <span class="font-black text-lg">{{ val }}</span>
                    </div>
                </div>
            </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>