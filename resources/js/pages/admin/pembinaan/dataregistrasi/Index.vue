<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{
  dataregistrasis: any, upts: any[], filters: { upt_id?: string, tanggal?: string },
  umums: any[], pidsuses: any[], pidums: any[], overstayings: any[], integrasis: any[], identitases: any[], agamas: any[], pengeluarans: any[],
  pendidikans: any[], detail_napis: any[], detail_tahanans: any[], petugases: any[], pengunjungs: any[], wbp_dikunjungis: any[], wbp_vidcalls: any[], wbp_wartels: any[], barang_titipans: any[]
}>()

const page = usePage()
const filterUpt = ref(props.filters.upt_id || '')
const filterTanggal = ref(props.filters.tanggal || '')

const isModalOpen = ref(false)
const selectedData = ref<any | null>(null)
const activeTab = ref('registrasi') // Tab selector mewah es: 'registrasi' atau 'kunjungan'

function showDetail(data: any) {
  selectedData.value = data
  activeTab.value = 'registrasi' 
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  setTimeout(() => { selectedData.value = null }, 200)
}

watch([filterUpt, filterTanggal], debounce(([newFilterUpt, newFilterTanggal]) => {
    const params = new URLSearchParams();
    if (newFilterUpt) params.append('upt_id', newFilterUpt);
    if (newFilterTanggal) params.append('tanggal', newFilterTanggal);
    router.get(window.location.pathname, Object.fromEntries(params), { preserveState: true, preserveScroll: true, replace: true });
}, 300))

function destroyData(id: number) {
  if (!confirm('Hapus rekap ini?')) return
  router.delete(`/admin/data-registrasis/${id}`)
}

function findName(array: any[], id: string | number, keyName: string) {
  const item = array?.find(i => String(i.id) === String(id));
  return item ? item[keyName] : '-';
}
</script>

<template>
  <Head title="Rekap Registrasi Harian" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Rekap Registrasi Harian</h1>
          <p class="text-sm text-muted-foreground">Kelola laporan terintegrasi WBP dan Kunjungan.</p>
        </div>
        <Link href="/admin/data-registrasis/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow">+ Buat Laporan Baru</Link>
      </div>

      <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
        <input v-model="filterTanggal" type="date" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
        <select v-model="filterUpt" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
          <option value="">Semua Lapas / UPT</option>
          <option v-for="upt in upts" :key="upt.id" :value="String(upt.id)">{{ upt.name }}</option>
        </select>
      </div>

      <div class="rounded-xl border bg-card text-card-foreground shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-muted/50 border-b">
            <tr>
              <th class="h-12 px-4 text-left font-medium">Tanggal Laporan</th>
              <th class="h-12 px-4 text-left font-medium">Lokasi UPT</th>
              <th class="h-12 px-4 text-right font-medium">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in dataregistrasis.data" :key="d.id" class="border-b hover:bg-muted/30">
              <td class="p-4 font-bold text-primary">{{ d.tanggal }}</td>
              <td class="p-4">{{ d.upt?.name ?? '-' }}</td>
              <td class="p-4 text-right">
                <div class="flex justify-end gap-2">
                  <button type="button" @click="showDetail(d)" class="border px-3 h-8 rounded-md text-xs font-medium hover:bg-blue-50 hover:text-blue-600">Lihat Detail</button>
                  <Link :href="`/admin/data-registrasis/${d.id}/edit`" class="border px-3 h-8 rounded-md text-xs font-medium hover:bg-accent">Edit</Link>
                  <button type="button" @click="destroyData(d.id)" class="bg-destructive text-white px-3 h-8 rounded-md text-xs font-medium">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @click.self="closeModal">
      <div class="w-full max-w-6xl max-h-[90vh] overflow-y-auto rounded-xl border bg-card p-6 shadow-xl flex flex-col">
        
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between border-b pb-4 mb-4 sticky top-0 bg-card z-10">
          <div>
              <h2 class="text-xl font-bold text-primary">Berkas Rekapitulasi Harian</h2>
              <p class="text-xs text-muted-foreground">{{ selectedData?.upt?.name }} — {{ selectedData?.tanggal }}</p>
          </div>
          
          <div class="flex bg-muted p-1 rounded-lg border">
              <button @click="activeTab = 'registrasi'" :class="activeTab === 'registrasi' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground'" class="px-4 py-1.5 rounded-md text-xs font-bold transition-all">📊 Rekap Registrasi Utama</button>
              <button @click="activeTab = 'kunjungan'" :class="activeTab === 'kunjungan' ? 'bg-background text-orange-700 shadow-sm' : 'text-muted-foreground hover:text-orange-600'" class="px-4 py-1.5 rounded-md text-xs font-bold transition-all">📋 Laporan Kunjungan Harian</button>
          </div>
        </div>

        <div v-if="selectedData && activeTab === 'registrasi'" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 animate-fadeIn">
            <div class="rounded-lg bg-muted/30 p-3 border flex flex-col justify-between">
                <div>
                    <h4 class="font-bold text-xs text-primary mb-2 border-b pb-1">Data Umum</h4>
                    <ul class="space-y-1">
                        <li v-for="(v, k) in selectedData.rekap_umum" :key="k" class="flex justify-between text-xs">
                            <template v-if="k !== 'detail_wna'">
                                <span class="text-muted-foreground">{{ findName(umums, k, 'nama_registrasiumum') }}</span>
                                <span class="font-bold" :class="findName(umums, k, 'nama_registrasiumum').toLowerCase().includes('overcrowded') ? 'text-red-600':''">{{ v }}</span>
                            </template>
                        </li>
                    </ul>
                </div>
                
                <div v-if="selectedData.rekap_umum['detail_wna'] && selectedData.rekap_umum['detail_wna'].length > 0" class="mt-3 p-2 bg-blue-50 rounded border border-blue-100 text-[11px]">
                    <p class="font-bold text-blue-800 mb-1">🌐 Rincian Kewarganegaraan WNA:</p>
                    <div v-for="(wna, i) in selectedData.rekap_umum['detail_wna']" :key="i" class="flex justify-between text-[10px] bg-white px-2 py-0.5 rounded mb-1 border">
                        <span class="font-semibold text-slate-700">{{ wna.negara }}</span>
                        <span class="font-bold bg-blue-100 px-1 rounded text-blue-800">{{ wna.status }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-purple-50/40 p-3 border border-purple-100">
                <h4 class="font-bold text-xs text-purple-700 mb-2 border-b pb-1">Tingkat Pendidikan</h4>
                <ul class="space-y-1"><li v-for="(v, k) in selectedData.rekap_pendidikan" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(pendidikans, k, 'nama_registrasipendidikan') }}</span><span class="font-bold text-purple-700">{{ v }}</span></li></ul>
            </div>
            <div class="rounded-lg bg-emerald-50/40 p-3 border border-emerald-100">
                <h4 class="font-bold text-xs text-emerald-700 mb-2 border-b pb-1">Detail Rekap Napi</h4>
                <ul class="space-y-1"><li v-for="(v, k) in selectedData.rekap_detail_napi" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(detail_napis, k, 'nama_registrasidetailnapi') }}</span><span class="font-bold text-emerald-700">{{ v }}</span></li></ul>
            </div>
            <div class="rounded-lg bg-blue-50/40 p-3 border border-blue-100">
                <h4 class="font-bold text-xs text-blue-700 mb-2 border-b pb-1">Detail Rekap Tahanan</h4>
                <ul class="space-y-1"><li v-for="(v, k) in selectedData.rekap_detail_tahanan" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(detail_tahanans, k, 'nama_registrasidetailtahanan') }}</span><span class="font-bold text-blue-700">{{ v }}</span></li></ul>
            </div>
            
            <div class="rounded-lg bg-card border p-3"><h4 class="font-bold text-xs text-red-600 mb-2 border-b pb-1">Pidsus</h4><ul class="space-y-1"><li v-for="(v, k) in selectedData.rekap_pidsus" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(pidsuses, k, 'nama_registrasipidsus') }}</span><span class="font-bold">{{ v }}</span></li></ul></div>
            <div class="rounded-lg bg-card border p-3"><h4 class="font-bold text-xs text-orange-600 mb-2 border-b pb-1">Pidum</h4><ul class="space-y-1"><li v-for="(v, k) in selectedData.rekap_pidum" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(pidums, k, 'nama_registrasipidum') }}</span><span class="font-bold">{{ v }}</span></li></ul></div>
            <div class="rounded-lg bg-card border p-3"><h4 class="font-bold text-xs text-amber-600 mb-2 border-b pb-1">Overstaying</h4><ul class="space-y-1"><li v-for="(v, k) in selectedData.rekap_overstaying" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(overstayings, k, 'nama_registrasioverstaying') }}</span><span class="font-bold">{{ v }}</span></li></ul></div>
            <div class="rounded-lg bg-card border p-3"><h4 class="font-bold text-xs text-emerald-600 mb-2 border-b pb-1">Integrasi</h4><ul class="space-y-1"><li v-for="(v, k) in selectedData.rekap_integrasi" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(integrasis, k, 'nama_integrasi') }}</span><span class="font-bold">{{ v }}</span></li></ul></div>
            <div class="rounded-lg bg-card border p-3"><h4 class="font-bold text-xs text-blue-600 mb-2 border-b pb-1">Kependudukan</h4><ul class="space-y-1"><li v-for="(v, k) in selectedData.rekap_identitas" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(identitases, k, 'nama_registrasiidentitas') }}</span><span class="font-bold">{{ v }}</span></li></ul></div>
            <div class="rounded-lg bg-card border p-3"><h4 class="font-bold text-xs text-indigo-600 mb-2 border-b pb-1">Agama</h4><ul class="space-y-1"><li v-for="(v, k) in selectedData.rekap_agama" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(agamas, k, 'nama_agama') }}</span><span class="font-bold">{{ v }}</span></li></ul></div>
            <div class="rounded-lg bg-card border p-3"><h4 class="font-bold text-xs text-teal-600 mb-2 border-b pb-1">Pengeluaran</h4><ul class="space-y-1"><li v-for="(v, k) in selectedData.rekap_pengeluaran" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(pengeluarans, k, 'nama_pengeluaran') }}</span><span class="font-bold">{{ v }}</span></li></ul></div>
        </div>

        <div v-if="selectedData && activeTab === 'kunjungan'" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 animate-fadeIn">
            <div class="rounded-xl bg-orange-50/30 p-4 border border-orange-100 shadow-sm"><h4 class="font-bold text-xs text-orange-700 mb-3 border-b border-orange-200 pb-1">Daftar Petugas Jaga</h4>
                <ul class="space-y-1.5"><li v-for="(v, k) in selectedData.rekap_petugas" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(petugases, k, 'nama_registrasipetugas') }}</span><span class="font-black text-orange-800 bg-white px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul>
            </div>
            <div class="rounded-xl bg-orange-50/30 p-4 border border-orange-100 shadow-sm"><h4 class="font-bold text-xs text-orange-700 mb-3 border-b border-orange-200 pb-1">Daftar Pengunjung</h4>
                <ul class="space-y-1.5"><li v-for="(v, k) in selectedData.rekap_pengunjung" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(pengunjungs, k, 'nama_registrasipengunjung') }}</span><span class="font-black text-orange-800 bg-white px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul>
            </div>
            <div class="rounded-xl bg-orange-50/30 p-4 border border-orange-100 shadow-sm"><h4 class="font-bold text-xs text-orange-700 mb-3 border-b border-orange-200 pb-1">WBP Yang Dikunjungi</h4>
                <ul class="space-y-1.5"><li v-for="(v, k) in selectedData.rekap_wbp_dikunjungi" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(wbp_dikunjungis, k, 'nama_registrasiwbpdikunjungi') }}</span><span class="font-black text-orange-800 bg-white px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul>
            </div>
            <div class="rounded-xl bg-orange-50/30 p-4 border border-orange-100 shadow-sm"><h4 class="font-bold text-xs text-orange-700 mb-3 border-b border-orange-200 pb-1">Layanan Video Call</h4>
                <ul class="space-y-1.5"><li v-for="(v, k) in selectedData.rekap_wbp_vidcall" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(wbp_vidcalls, k, 'nama_registrasiwbpvidcall') }}</span><span class="font-black text-orange-800 bg-white px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul>
            </div>
            <div class="rounded-xl bg-orange-50/30 p-4 border border-orange-100 shadow-sm"><h4 class="font-bold text-xs text-orange-700 mb-3 border-b border-orange-200 pb-1">Layanan Wartel</h4>
                <ul class="space-y-1.5"><li v-for="(v, k) in selectedData.rekap_wbp_wartel" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(wbp_wartels, k, 'nama_registrasiwbpwartel') }}</span><span class="font-black text-orange-800 bg-white px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul>
            </div>
            <div class="rounded-xl bg-orange-50/30 p-4 border border-orange-100 shadow-sm"><h4 class="font-bold text-xs text-orange-700 mb-3 border-b border-orange-200 pb-1">Titipan Barang & Makanan</h4>
                <ul class="space-y-1.5"><li v-for="(v, k) in selectedData.rekap_barang_titipan" :key="k" class="flex justify-between text-xs"><span class="text-muted-foreground">{{ findName(barang_titipans, k, 'nama_registrasibarangtitipan') }}</span><span class="font-black text-orange-800 bg-white px-2 py-0.5 rounded shadow-sm">{{ v }}</span></li></ul>
            </div>
        </div>

        <div class="mt-6 flex justify-end pt-4 border-t bg-card"><button @click="closeModal" class="rounded-lg bg-secondary px-6 py-2 text-sm font-medium">Tutup Berkas</button></div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.animate-fadeIn { animation: fadeIn 0.25s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
</style>