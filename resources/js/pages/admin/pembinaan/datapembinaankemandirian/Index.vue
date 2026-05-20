<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'
import { can } from '@/lib/can'

type Upt = { id: number; name: string }
type JenisKemandirian = { id: number; nama_kemandirian: string }

type DataKemandirian = {
  id: number
  tanggal: string
  nama_kegiatan: string
  jumlah_peserta: number
  hasil_kegiatan: string
  rekomendasi_kegiatan: string
  dokumentasi_kegiatan: string[]
  upt?: Upt
  jenis_kemandirian?: JenisKemandirian
}

const props = defineProps<{
  datapembinaankemandirians: { data: DataKemandirian[], links: any[], from: number, to: number, total: number }
  upts: Upt[]
  filters: { search?: string, upt_id?: string, tanggal?: string }
}>()

const page = usePage()

const search = ref(props.filters.search || '')
const filterUpt = ref(props.filters.upt_id || '')
const filterTanggal = ref(props.filters.tanggal || '')

const isModalOpen = ref(false)
const selectedData = ref<DataKemandirian | null>(null)

function showDetail(data: DataKemandirian) {
  selectedData.value = data
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  setTimeout(() => { selectedData.value = null }, 200)
}

function isVideo(path: string) {
  return path.match(/\.(mp4|mov)$/i);
}

watch(
  [search, filterUpt, filterTanggal],
  debounce(([newSearch, newFilterUpt, newFilterTanggal]) => {
    const params = new URLSearchParams();
    if (newSearch) params.append('search', newSearch);
    if (newFilterUpt) params.append('upt_id', newFilterUpt);
    if (newFilterTanggal) params.append('tanggal', newFilterTanggal);
    
    router.get(window.location.pathname, Object.fromEntries(params), { preserveState: true, preserveScroll: true, replace: true });
  }, 300)
)

function destroyData(id: number) {
  if (!confirm('Yakin mau hapus Data ini?')) return
  router.delete(`/admin/data-pembinaan-kemandirians/${id}`)
}
</script>

<template>
  <Head title="Pembinaan Kemandirian" />

  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Pembinaan Kemandirian</h1>
          <p class="text-sm text-muted-foreground">Kelola data kegiatan kemandirian</p>
        </div>
        <Link v-if="can('PembinaanKemandirians.create')" href="/admin/data-pembinaan-kemandirians/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:opacity-90 transition-colors">+ Tambah Kegiatan</Link>
      </div>

      <div v-if="page.props.flash?.success" class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600 dark:text-emerald-400">
        {{ page.props.flash.success }}
      </div>

      <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
        <input v-model="search" type="text" placeholder="Cari Kegiatan..." class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
        <input v-model="filterTanggal" type="date" class="col-span-1 md:col-span-1 lg:col-span-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring cursor-pointer" />
        <select v-model="filterUpt" class="col-span-1 md:col-span-1 lg:col-span-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring cursor-pointer">
          <option value="">Semua Lapas / UPT</option>
          <option v-for="upt in upts" :key="upt.id" :value="String(upt.id)">{{ upt.name }}</option>
        </select>
      </div>

      <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm overflow-hidden">
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm">
            <thead class="[&_tr]:border-b border-border">
              <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground w-[100px]">Tanggal</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Nama Kegiatan</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">UPT</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Jenis</th>
                <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody class="[&_tr:last-child]:border-0">
              <tr v-for="d in datapembinaankemandirians.data" :key="d.id" class="border-b border-border transition-colors hover:bg-muted/50">
                <td class="p-4 text-muted-foreground">{{ d.tanggal }}</td>
                <td class="p-4"><div class="font-bold text-primary">{{ d.nama_kegiatan }}</div></td>
                <td class="p-4 text-muted-foreground">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4">
                  <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                    {{ d.jenis_kemandirian?.nama_kemandirian ?? '-' }} 
                  </span>
                </td>
                <td class="p-4 text-right align-middle">
                  <div class="flex justify-end gap-2">
                    <button type="button" @click="showDetail(d)" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-transparent shadow-sm hover:bg-blue-50 hover:text-blue-600 h-8 px-3">Lihat</button>
                    <Link v-if="can('PembinaanKemandirians.edit')" :href="`/admin/data-pembinaan-kemandirians/${d.id}/edit`" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-transparent shadow-sm hover:bg-accent hover:text-accent-foreground h-8 px-3">Edit</Link>
                    <button v-if="can('PembinaanKemandirians.delete')"  type="button" @click="destroyData(d.id)" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90 h-8 px-3">Hapus</button>
                  </div>
                </td>
              </tr>
              <tr v-if="!datapembinaankemandirians.data.length">
                <td colspan="5" class="p-8 text-center text-muted-foreground">Tidak ada data ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div v-if="datapembinaankemandirians.links.length > 3" class="flex items-center justify-between mt-2">
        <div class="text-sm text-muted-foreground">Menampilkan {{ datapembinaankemandirians.from ?? 0 }} sampai {{ datapembinaankemandirians.to ?? 0 }} dari {{ datapembinaankemandirians.total }} data</div>
        <div class="flex gap-1">
          <template v-for="(link, key) in datapembinaankemandirians.links" :key="key">
            <div v-if="link.url === null" class="px-3 py-1 text-sm text-muted-foreground border border-transparent" v-html="link.label" />
            <Link v-else :href="link.url" preserve-state preserve-scroll class="px-3 py-1 text-sm rounded-md border transition-colors" :class="link.active ? 'bg-primary text-primary-foreground border-primary' : 'border-border bg-background hover:bg-accent hover:text-accent-foreground'" v-html="link.label" />
          </template>
        </div>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm transition-opacity" @click.self="closeModal">
      <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-xl border border-border bg-card p-6 text-card-foreground shadow-lg">
        <div class="flex items-center justify-between border-b pb-3 mb-4 sticky top-0 bg-card z-10">
          <h2 class="text-lg font-semibold">Detail Kegiatan Kemandirian</h2>
          <button @click="closeModal" class="text-muted-foreground hover:text-foreground">&times; Tutup</button>
        </div>
        
        <div v-if="selectedData" class="grid gap-4 md:grid-cols-2 text-sm">
          <div><p class="text-muted-foreground text-xs mb-1">Tanggal Kegiatan</p><p class="font-medium">{{ selectedData.tanggal }}</p></div>
          <div><p class="text-muted-foreground text-xs mb-1">Nama Kegiatan</p><p class="font-medium text-primary">{{ selectedData.nama_kegiatan }}</p></div>
          <div><p class="text-muted-foreground text-xs mb-1">Lokasi UPT</p><p class="font-medium">{{ selectedData.upt?.name ?? '-' }}</p></div>
          <div>
              <p class="text-muted-foreground text-xs mb-1">Jenis Kemandirian</p>
              <p class="font-medium text-blue-700">
                  {{ selectedData.jenis_kemandirian?.nama_kemandirian ?? '-' }}
                  <span v-if="selectedData.detail_lain_lain" class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded border border-orange-200 ml-1">
                      ({{ selectedData.detail_lain_lain }})
                  </span>
              </p>
          </div>
          <div><p class="text-muted-foreground text-xs mb-1">Jumlah Peserta</p><p class="font-medium">{{ selectedData.jumlah_peserta }} Orang</p></div>
          
          <div class="col-span-2 mt-2 rounded-lg bg-muted/50 p-3">
            <h3 class="font-semibold mb-2 border-b pb-1">Hasil & Rekomendasi</h3>
            <p class="text-muted-foreground text-xs mb-1">Hasil Kegiatan</p>
            <p class="font-medium mb-3">{{ selectedData.hasil_kegiatan }}</p>
            <p class="text-muted-foreground text-xs mb-1">Rekomendasi Kegiatan</p>
            <p class="font-medium">{{ selectedData.rekomendasi_kegiatan || '-' }}</p>
          </div>

          <div class="col-span-2 mt-2" v-if="selectedData.dokumentasi_kegiatan?.length">
            <h3 class="font-semibold mb-2">Dokumentasi ({{ selectedData.dokumentasi_kegiatan.length }} File)</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
              <div v-for="(file, index) in selectedData.dokumentasi_kegiatan" :key="index" class="relative aspect-video rounded-md overflow-hidden bg-black/10 border border-border">
                <video v-if="isVideo(file)" :src="`/storage/${file}`" controls class="w-full h-full object-cover"></video>
                <a v-else :href="`/storage/${file}`" target="_blank">
                  <img :src="`/storage/${file}`" class="w-full h-full object-cover hover:scale-105 transition-transform" />
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end pt-4 border-t">
          <button @click="closeModal" class="rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80">Tutup</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>