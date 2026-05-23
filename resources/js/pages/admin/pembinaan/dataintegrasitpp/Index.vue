<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{
  dataintegrasitpps: any
  upts: any[]
  filters: { search?: string, upt_id?: string }
}>()

const page = usePage()
const search = ref(props.filters.search || '')
const filterUpt = ref(props.filters.upt_id || '')

const isModalOpen = ref(false)
const selectedData = ref<any | null>(null)

function showDetail(data: any) {
  selectedData.value = data
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  setTimeout(() => { selectedData.value = null }, 200)
}

watch([search, filterUpt], debounce(([newSearch, newFilterUpt]) => {
  const params = new URLSearchParams();
  if (newSearch) params.append('search', newSearch);
  if (newFilterUpt) params.append('upt_id', newFilterUpt);
  router.get(window.location.pathname, Object.fromEntries(params), { preserveState: true, preserveScroll: true, replace: true });
}, 300))

function destroyData(id: number) {
  if (!confirm('Yakin mau hapus data Sidang TPP ini?')) return
  router.delete(`/admin/data-integrasi-tpps/${id}`)
}
</script>

<template>
  <Head title="Data TPP UPT" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Data Sidang TPP UPT</h1>
          <p class="text-sm text-muted-foreground">Kelola laporan pelaksanaan Sidang TPP.</p>
        </div>
        <Link href="/admin/data-integrasi-tpps/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90">+ Input Sidang TPP</Link>
      </div>

      <div v-if="page.props.flash?.success" class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600">
        {{ page.props.flash.success }}
      </div>

      <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
        <input v-model="search" type="text" placeholder="Cari No. Sidang..." class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
        <select v-model="filterUpt" class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm cursor-pointer">
          <option value="">Semua Lapas / UPT</option>
          <option v-for="upt in upts" :key="upt.id" :value="String(upt.id)">{{ upt.name }}</option>
        </select>
      </div>

      <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm overflow-hidden">
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm">
            <thead class="[&_tr]:border-b border-border">
              <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                <th class="h-12 px-4 text-left font-medium text-muted-foreground">Pelaksanaan</th>
                <th class="h-12 px-4 text-left font-medium text-muted-foreground">Nomor Sidang</th>
                <th class="h-12 px-4 text-left font-medium text-muted-foreground">UPT</th>
                <th class="h-12 px-4 text-center font-medium text-muted-foreground">Jml Usulan</th>
                <th class="h-12 px-4 text-right font-medium text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody class="[&_tr:last-child]:border-0">
              <tr v-for="d in dataintegrasitpps.data" :key="d.id" class="border-b transition-colors hover:bg-muted/50">
                <td class="p-4 text-muted-foreground">{{ d.tanggal_pelaksanaan }}</td>
                <td class="p-4 font-bold text-primary">{{ d.nomor_sidang }}</td>
                <td class="p-4 text-muted-foreground">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 text-center font-bold">{{ d.jumlah_narapidana_sidang }} Org</td>
                <td class="p-4 text-right align-middle">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="inline-flex items-center rounded-md text-sm font-medium border border-input bg-transparent hover:bg-blue-50 hover:text-blue-600 h-8 px-3">Lihat</button>
                    <Link :href="`/admin/data-integrasi-tpps/${d.id}/edit`" class="inline-flex items-center rounded-md text-sm font-medium border border-input bg-transparent hover:bg-accent h-8 px-3">Edit</Link>
                    <button @click="destroyData(d.id)" class="inline-flex items-center rounded-md text-sm font-medium bg-destructive text-white hover:bg-destructive/90 h-8 px-3">Hapus</button>
                  </div>
                </td>
              </tr>
              <tr v-if="!dataintegrasitpps.data.length"><td colspan="5" class="p-8 text-center text-muted-foreground">Tidak ada data.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @click.self="closeModal">
      <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-xl bg-card p-6 shadow-lg">
        <h2 class="text-lg font-semibold border-b pb-3 mb-4">Detail Laporan Sidang TPP</h2>
        <div v-if="selectedData" class="grid gap-6 md:grid-cols-2 text-sm">
          
          <div class="space-y-4">
              <div><p class="text-xs text-muted-foreground">Lokasi UPT</p><p class="font-bold">{{ selectedData.upt?.name }}</p></div>
              <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs text-muted-foreground">Tgl Input</p><p class="font-medium">{{ selectedData.tanggal_input }}</p></div>
                <div><p class="text-xs text-muted-foreground">Tgl Pelaksanaan</p><p class="font-medium">{{ selectedData.tanggal_pelaksanaan }}</p></div>
                <div><p class="text-xs text-muted-foreground">Jml Narapidana Mengikuti Sidang</p><p class="font-bold text-lg text-primary">{{ selectedData.jumlah_narapidana_sidang }} Orang</p></div></div>
              <div><p class="text-xs text-muted-foreground">Nomor Sidang</p><p class="font-bold text-primary">{{ selectedData.nomor_sidang }}</p></div>            
              <div class="bg-muted/50 p-3 rounded-lg space-y-3">
                  <div><p class="text-xs text-muted-foreground">Rekomendasi Sidang</p><p>{{ selectedData.rekomendiasi_sidang }}</p></div>
                  <div><p class="text-xs text-muted-foreground border-t pt-2">Permasalahan</p><p class="text-red-600">{{ selectedData.permasalahan }}</p></div>
                  <div><p class="text-xs text-muted-foreground border-t pt-2">Upaya</p><p class="text-emerald-600">{{ selectedData.upaya }}</p></div>
              </div>
          </div>

          <div class="space-y-4 border-l pl-6">
              <h3 class="font-bold text-primary">Lampiran Dokumen</h3>
              
              <div class="bg-blue-50/50 dark:bg-blue-900/10 p-3 rounded-lg border border-blue-100">
                  <p class="text-xs font-semibold mb-2">📄 Berita Acara Sidang (PDF)</p>
                  <a :href="`/view-file?path=${selectedData.berita_acara}`" target="_blank" class="text-blue-600 hover:underline text-xs">Download / Lihat PDF Berita Acara</a>
              </div>

              <div class="bg-indigo-50/50 dark:bg-indigo-900/10 p-3 rounded-lg border border-indigo-100">
                  <p class="text-xs font-semibold mb-2">📋 Absensi Peserta (PDF)</p>
                  <a :href="`/view-file?path=${selectedData.absensi}`" target="_blank" class="text-indigo-600 hover:underline text-xs">Download / Lihat PDF Absensi</a>
              </div>

              <div class="bg-muted/30 p-3 rounded-lg border border-border">
                  <p class="text-xs font-semibold mb-2">🖼️ Dokumentasi Sidang ({{ selectedData.dokumentasi_sidang?.length || 0 }} Foto)</p>
                  <div class="grid grid-cols-3 gap-2">
                      <a v-for="(img, idx) in selectedData.dokumentasi_sidang" :key="idx" :href="`/view-file?path=${img}`" target="_blank">
                          <img :src="`/view-file?path=${img}`" class="w-full h-20 object-cover rounded border" />
                      </a>
                  </div>
              </div>
          </div>

        </div>
        <div class="mt-6 text-right"><button @click="closeModal" class="rounded bg-secondary px-4 py-2 text-sm hover:bg-secondary/80">Tutup</button></div>
      </div>
    </div>
  </AppLayout>
</template>