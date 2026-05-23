<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{ dataintegrasis: any, upts: any[], filters: { tanggal?: string, upt_id?: string } }>()

const page = usePage()
const filterUpt = ref(props.filters.upt_id || '')
const filterTanggal = ref(props.filters.tanggal || '')

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

watch([filterUpt, filterTanggal], debounce(([newUpt, newTanggal]) => {
  const params = new URLSearchParams();
  if (newUpt) params.append('upt_id', newUpt);
  if (newTanggal) params.append('tanggal', newTanggal);
  router.get(window.location.pathname, Object.fromEntries(params), { preserveState: true, preserveScroll: true, replace: true });
}, 300))

function destroyData(id: number) {
  if (!confirm('Yakin mau hapus laporan Integrasi ini?')) return
  router.delete(`/admin/data-integrasis/${id}`)
}
</script>

<template>
  <Head title="Data Integrasi" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Laporan Integrasi WBP</h1>
          <p class="text-sm text-muted-foreground">Kelola laporan pelaksanaan PB, CB, CMB, dan Asimilasi.</p>
        </div>
        <Link href="/admin/data-integrasis/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90">+ Input Integrasi</Link>
      </div>

      <div v-if="page.props.flash?.success" class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600">
        {{ page.props.flash.success }}
      </div>

      <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
        <input v-model="filterTanggal" type="date" class="col-span-1 md:col-span-2 lg:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
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
                <th class="h-12 px-4 text-left font-medium text-muted-foreground">Tgl Input</th>
                <th class="h-12 px-4 text-left font-medium text-muted-foreground">Lokasi UPT</th>
                <th class="h-12 px-4 text-center font-medium text-muted-foreground">Total PB/CB/CMB/Asimilasi</th>
                <th class="h-12 px-4 text-right font-medium text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody class="[&_tr:last-child]:border-0">
              <tr v-for="d in dataintegrasis.data" :key="d.id" class="border-b transition-colors hover:bg-muted/50">
                <td class="p-4 font-bold text-primary">{{ d.tanggal_input }}</td>
                <td class="p-4 text-muted-foreground">{{ d.upt?.name ?? '-' }}</td>
                <td class="p-4 text-center font-medium">{{ d.jumlah_pb + d.jumlah_cb + d.jumlah_cmb + d.jumlah_asimilasi + d.jumlah_bebas_murni + d.jumlah_cmk }} Orang</td>
                <td class="p-4 text-right align-middle">
                  <div class="flex justify-end gap-2">
                    <button @click="showDetail(d)" class="inline-flex items-center rounded-md text-sm font-medium border border-input bg-transparent hover:bg-blue-50 hover:text-blue-600 h-8 px-3">Lihat</button>
                    <Link :href="`/admin/data-integrasis/${d.id}/edit`" class="inline-flex items-center rounded-md text-sm font-medium border border-input bg-transparent hover:bg-accent h-8 px-3">Edit</Link>
                    <button @click="destroyData(d.id)" class="inline-flex items-center rounded-md text-sm font-medium bg-destructive text-white hover:bg-destructive/90 h-8 px-3">Hapus</button>
                  </div>
                </td>
              </tr>
              <tr v-if="!dataintegrasis.data.length"><td colspan="4" class="p-8 text-center text-muted-foreground">Tidak ada data integrasi.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @click.self="closeModal">
      <div class="w-full max-w-5xl max-h-[90vh] overflow-y-auto rounded-xl bg-card p-6 shadow-lg">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h2 class="text-lg font-semibold">Laporan Integrasi: {{ selectedData?.upt?.name }} ({{ selectedData?.tanggal_input }})</h2>
            <button @click="closeModal" class="text-muted-foreground hover:text-foreground">&times; Tutup</button>
        </div>
        
        <div v-if="selectedData" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 text-sm">
            <div v-for="jenis in [
                {key: 'pb', label: 'Pembebasan Bersyarat (PB)', color: 'blue'}, 
                {key: 'cb', label: 'Cuti Bersyarat (CB)', color: 'orange'}, 
                {key: 'cmb', label: 'Cuti Menjelang Bebas (CMB)', color: 'emerald'}, 
                {key: 'asimilasi', label: 'Asimilasi', color: 'indigo'},
                {key: 'bebas_murni', label: 'Bebas Murni', color: 'red'},
                {key: 'cmk', label: 'Cuti Menjelang Keluarga (CMK)', color: 'purple'}
              ]" :key="jenis.key" class="bg-muted/30 p-4 rounded-lg border"
            >
                <h3 :class="`font-bold text-${jenis.color}-600 mb-3 border-b pb-2`">{{ jenis.label }}</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <div><p class="text-xs text-muted-foreground">Tgl Pelaksanaan</p><p class="font-medium">{{ selectedData[`tanggal_pelaksanaan_${jenis.key}`] }}</p></div>
                        <div class="text-right"><p class="text-xs text-muted-foreground">Jml Orang</p><p class="font-bold text-lg">{{ selectedData[`jumlah_${jenis.key}`] }}</p></div>
                    </div>
                    
                    <div class="pt-2 border-t">
                        <a v-if="selectedData[`sk_${jenis.key}`]" :href="`/view-file?path=${selectedData[`sk_${jenis.key}`]}`" target="_blank" class="text-blue-600 font-semibold text-xs hover:underline">📄 Lihat File SK PDF</a>
                        <span v-else class="text-xs text-red-500 italic">SK Belum Diupload</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t">
                      <a v-for="(foto, index) in selectedData[`dokumentasi_${jenis.key}`]" :key="index" :href="`/view-file?path=${foto}`" target="_blank">
                        <img :src="`/view-file?path=${foto}`" class="w-full h-24 object-cover rounded border hover:opacity-80" />
                      </a>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>