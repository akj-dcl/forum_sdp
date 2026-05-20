<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ datapublikasisdms: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

const filterUpt = ref(props.filters.upt_id || '')
const filterTanggal = ref(props.filters.tanggal || '') // Filter berdasarkan tanggal publikasi
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
  if (!confirm('Hapus data publikasi kegiatan ini?')) return
  router.delete(`/admin/data-publikasi-sdms/${id}`)
}
</script>

<template>
  <Head title="Data Publikasi" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight text-primary">Data Publikasi & Humas</h1></div>
        <Link href="/admin/data-publikasi-sdms/create" class="inline-flex items-center justify-center rounded-md bg-primary text-white px-4 py-2 text-sm font-bold shadow hover:bg-primary/90">+ Input Publikasi Baru</Link>
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
                <th class="h-12 px-4 text-left font-bold text-muted-foreground w-32">Tgl Publikasi</th>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">UPT</th>
                <th class="h-12 px-4 text-left font-bold text-primary">Nama Kegiatan</th>
                <th class="h-12 px-4 text-left font-bold text-muted-foreground">Jenis Media</th>
                <th class="h-12 px-4 text-right font-bold text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in datapublikasisdms.data" :key="d.id" class="border-b hover:bg-muted/30">
                <td class="p-4 font-bold">{{ d.tanggal_publikasi }}</td>
                <td class="p-4 font-medium">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 font-bold text-primary max-w-xs truncate" :title="d.nama_kegiatan">{{ d.nama_kegiatan }}</td>
                <td class="p-4"><span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-[10px] font-bold uppercase">{{ d.media_publikasi }}</span></td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Detail</button>
                    <Link :href="`/admin/data-publikasi-sdms/${d.id}/edit`" class="px-3 py-1 border rounded-md hover:bg-muted text-xs font-medium">Edit</Link>
                    <button @click="destroyData(d.id)" class="px-3 py-1 bg-red-600 text-white rounded-md text-xs font-bold">Hapus</button>
                  </div>
                </td>
              </tr>
              <tr v-if="!datapublikasisdms.data.length">
                  <td colspan="5" class="p-8 text-center text-muted-foreground italic">Belum ada data publikasi.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @click.self="closeModal">
      <div class="w-full max-w-3xl rounded-xl bg-card shadow-lg flex flex-col max-h-[90vh] overflow-hidden border border-border">
        
        <div class="flex justify-between items-center border-b border-border p-5 bg-muted/20">
            <div>
                <h2 class="text-lg font-black text-primary">Detail Berita & Publikasi</h2>
                <p class="text-sm text-muted-foreground">{{ selectedData?.upt?.name }}</p>
            </div>
            <button @click="closeModal" class="text-2xl leading-none text-muted-foreground hover:text-red-500">&times;</button>
        </div>
        
        <div class="p-6 space-y-6 overflow-y-auto">
            
            <div class="text-center pb-4 border-b border-border">
                <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-1">{{ selectedData?.tanggal_publikasi }}</p>
                <h3 class="text-2xl font-black text-foreground">{{ selectedData?.nama_kegiatan }}</h3>
                <span class="inline-block mt-3 bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold uppercase">{{ selectedData?.media_publikasi }}</span>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                <p class="text-xs font-bold text-slate-500 uppercase mb-2">Uraian Singkat Kegiatan</p>
                <p class="text-sm leading-relaxed text-slate-800 text-justify whitespace-pre-wrap">{{ selectedData?.uraian_singkat }}</p>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 flex flex-col items-center justify-center text-center">
                <p class="text-xs font-bold text-blue-700 uppercase mb-2">Tautan Berita Online / Sosial Media</p>
                
                <template v-if="selectedData?.link_berita">
                    <p class="text-sm text-muted-foreground mb-4 break-all px-4">{{ selectedData?.link_berita }}</p>
                    <a :href="selectedData?.link_berita" target="_blank" class="bg-blue-600 text-white px-6 py-2 rounded-md font-bold shadow hover:bg-blue-700 transition-colors">Buka Tautan Berita</a>
                </template>
                <template v-else>
                    <p class="text-sm text-muted-foreground italic py-2">Tidak ada tautan (link) yang dicantumkan.</p>
                </template>
            </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>