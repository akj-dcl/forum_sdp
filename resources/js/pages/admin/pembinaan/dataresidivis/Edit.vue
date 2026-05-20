<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'
import { can } from '@/lib/can'

type Upt = { id: number; name: string }
type JenisPidana = { id: number; nama_pidana: string }

type DataResidivises = {
  id: number
  tanggal: string
  nama: string
  alamat: string | null
  jenis_pidana_sekarang_id: number
  lama_pidana_sekarang: string
  tempat_pidana_sekarang: string
  berapa_kali_dipidana: number // Tambahan Baru
  putusan_pengadilan: string | null // Tambahan Baru
  jenis_pidana_sebelumnya_id: number
  lama_pidana_sebelumnya: string
  tempat_pidana_sebelumnya: string
  jenis_pembebasan_sebelumnya: string
  upt?: Upt
  jenis_pidana_sekarang?: JenisPidana
  jenis_pidana_sebelumnya?: JenisPidana
}

// SISA SCRIPT (Props, State Filter, Fungsi Modal, Watcher) SAMA PERSIS SEPERTI SEBELUMNYA
type PaginationLink = { url: string | null; label: string; active: boolean }
const props = defineProps<{ dataresidivises: { data: DataResidivises[], links: PaginationLink[], from: number, to: number, total: number }, upts: Upt[], filters: { search?: string, upt_id?: string, tanggal?: string } }>()
const page = usePage()
const search = ref(props.filters.search || '')
const filterUpt = ref(props.filters.upt_id || '')
const filterTanggal = ref(props.filters.tanggal || '')
const isModalOpen = ref(false)
const selectedData = ref<DataResidivises | null>(null)

function showDetail(data: DataResidivises) { selectedData.value = data; isModalOpen.value = true; }
function closeModal() { isModalOpen.value = false; setTimeout(() => { selectedData.value = null }, 200); }

watch([search, filterUpt, filterTanggal], debounce(([newSearch, newFilterUpt, newFilterTanggal]) => {
    const params = new URLSearchParams();
    if (newSearch) params.append('search', newSearch);
    if (newFilterUpt) params.append('upt_id', newFilterUpt);
    if (newFilterTanggal) params.append('tanggal', newFilterTanggal);
    router.get(window.location.pathname, Object.fromEntries(params), { preserveState: true, preserveScroll: true, replace: true });
}, 300))

function destroyDataResidivis(id: number) {
  if (!confirm('Yakin mau hapus Data Residivis ini?')) return
  router.delete(`/admin/data-residivises/${id}`)
}
</script>

<template>
  <Head title="Master Residivis" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div><h1 class="text-2xl font-semibold tracking-tight">Data Residivis</h1><p class="text-sm text-muted-foreground">Kelola data pengulangan tindak pidana WBP</p></div>
        <Link v-if="can('data-residivises.create')" href="/admin/data-residivises/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow">+ Tambah Data Residivis</Link>
      </div>

      <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-5">
        <input v-model="search" type="text" placeholder="Cari nama..." class="col-span-1 md:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
        <input v-model="filterTanggal" type="date" class="col-span-1 md:col-span-1 lg:col-span-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
        <select v-model="filterUpt" class="col-span-1 md:col-span-2 lg:col-span-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
          <option value="">Semua Lapas / UPT</option>
          <option v-for="upt in upts" :key="upt.id" :value="String(upt.id)">{{ upt.name }}</option>
        </select>
      </div>

      <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm overflow-hidden">
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm">
            <thead class="bg-muted/50 border-b">
              <tr>
                <th class="h-12 px-4 text-left font-medium w-[100px]">Tanggal</th>
                <th class="h-12 px-4 text-left font-medium">Nama WBP</th>
                <th class="h-12 px-4 text-left font-medium">UPT</th>
                <th class="h-12 px-4 text-left font-medium text-red-600">Jml Pengulangan</th>
                <th class="h-12 px-4 text-right font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="w in dataresidivises.data" :key="w.id" class="border-b transition-colors hover:bg-muted/50">
                <td class="p-4 text-muted-foreground">{{ w.tanggal }}</td>
                <td class="p-4 font-bold text-primary">{{ w.nama }}</td>
                <td class="p-4 text-muted-foreground">{{ w.upt?.name ?? '-' }}</td>
                <td class="p-4"><span class="bg-red-100 text-red-800 px-2 py-0.5 rounded-full text-xs font-bold">{{ w.berapa_kali_dipidana }} Kali</span></td>
                <td class="p-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button type="button" @click="showDetail(w)" class="border h-8 px-3 rounded-md text-xs font-medium hover:bg-blue-50 hover:text-blue-600">Detail</button>
                    <Link v-if="can('data-residivises.edit')" :href="`/admin/data-residivises/${w.id}/edit`" class="border h-8 px-3 rounded-md text-xs font-medium hover:bg-accent">Edit</Link>
                    <button v-if="can('data-residivises.delete')" type="button" @click="destroyDataResidivis(w.id)" class="bg-destructive text-white h-8 px-3 rounded-md text-xs font-medium">Hapus</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @click.self="closeModal">
      <div class="w-full max-w-3xl rounded-xl border bg-card p-6 shadow-lg overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between border-b pb-3 mb-4">
          <h2 class="text-xl font-bold text-primary">Detail Berkas Residivis</h2>
          <button @click="closeModal" class="text-2xl text-muted-foreground hover:text-red-600">&times;</button>
        </div>
        
        <div v-if="selectedData" class="space-y-6">
          <div class="text-center bg-muted/30 p-4 rounded-xl border border-border">
              <p class="text-xs text-muted-foreground tracking-widest uppercase mb-1">{{ selectedData.tanggal }} | {{ selectedData.upt?.name ?? '-' }}</p>
              <h3 class="text-2xl font-black text-foreground">{{ selectedData.nama }}</h3>
              <p class="text-sm font-medium mt-1">{{ selectedData.alamat || 'Alamat tidak diketahui' }}</p>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
              <div class="border rounded-xl p-4 bg-red-50/50 border-red-100">
                  <h3 class="font-bold text-sm text-red-700 mb-3 border-b border-red-200 pb-1">A. Pidana Saat Ini</h3>
                  <div class="space-y-2 text-sm">
                      <div class="flex justify-between"><span class="text-muted-foreground">Jenis Pidana</span><span class="font-bold text-red-800">{{ selectedData.jenis_pidana_sekarang?.nama_pidana ?? '-' }}</span></div>
                      <div class="flex justify-between"><span class="text-muted-foreground">Lama Pidana</span><span class="font-semibold">{{ selectedData.lama_pidana_sekarang }} Bln/Thn</span></div>
                      <div class="flex justify-between"><span class="text-muted-foreground">Lokasi Pidana</span><span class="font-semibold">{{ selectedData.tempat_pidana_sekarang }}</span></div>
                      <div class="flex justify-between items-center mt-2 pt-2 border-t border-red-200">
                          <span class="font-bold text-red-600 text-xs">PENGULANGAN KE-</span>
                          <span class="bg-red-600 text-white px-2 py-0.5 rounded font-black">{{ selectedData.berapa_kali_dipidana }}</span>
                      </div>
                      
                      <div v-if="selectedData.putusan_pengadilan" class="mt-3 pt-3">
                          <a :href="`/storage/${selectedData.putusan_pengadilan}`" target="_blank" class="block text-center w-full bg-blue-600 text-white text-xs font-bold py-2 rounded shadow hover:bg-blue-700">Lihat PDF Putusan Pengadilan</a>
                      </div>
                  </div>
              </div>

              <div class="border rounded-xl p-4 bg-orange-50/50 border-orange-100">
                  <h3 class="font-bold text-sm text-orange-700 mb-3 border-b border-orange-200 pb-1">B. Pidana Sebelumnya</h3>
                  <div class="space-y-2 text-sm">
                      <div class="flex justify-between"><span class="text-muted-foreground">Jenis Pidana</span><span class="font-bold text-orange-800">{{ selectedData.jenis_pidana_sebelumnya?.nama_pidana ?? '-' }}</span></div>
                      <div class="flex justify-between"><span class="text-muted-foreground">Lama Pidana</span><span class="font-semibold">{{ selectedData.lama_pidana_sebelumnya }} Bln/Thn</span></div>
                      <div class="flex justify-between"><span class="text-muted-foreground">Lokasi Pidana</span><span class="font-semibold">{{ selectedData.tempat_pidana_sebelumnya }}</span></div>
                      <div class="flex justify-between mt-2 pt-2 border-t border-orange-200">
                          <span class="font-bold text-[10px] text-muted-foreground uppercase">Pembebasan Terakhir</span>
                      </div>
                      <div class="bg-white p-2 rounded border font-medium text-xs text-orange-900 text-center">{{ selectedData.jenis_pembebasan_sebelumnya }}</div>
                  </div>
              </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end pt-4 border-t">
          <button @click="closeModal" class="rounded-md bg-secondary px-6 py-2 text-sm font-medium hover:bg-secondary/80">Tutup Detail</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>