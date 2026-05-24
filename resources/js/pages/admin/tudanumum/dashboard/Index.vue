<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { debounce } from 'lodash'

const props = defineProps<{
    filters: any,
    upts: any[],
    highlight: any,
    data_dinamis: any[],
    summary: any,
}>()

const tanggal = ref(props.filters.tanggal)
const upt_id = ref(props.filters.upt_id)
const modul = ref(props.filters.modul)
const searchQuery = ref('')

watch([tanggal, upt_id, modul], debounce(([newTanggal, newUpt, newModul]) => {
    // Reset search query tiap kali ganti filter utama
    searchQuery.value = ''
    router.get(window.location.pathname, {
        tanggal: newTanggal,
        upt_id: newUpt,
        modul: newModul
    }, { preserveState: true, preserveScroll: true })
}, 300))

// 🛠️ Fungsi Computed untuk Filter Data secara Instan
const filteredData = computed(() => {
    if (!searchQuery.value) return props.data_dinamis;
    
    const q = searchQuery.value.toLowerCase();
    return props.data_dinamis.filter(item => {
        // Mencari ke semua isi text dalam objek baris data
        return Object.values(item).some(val => 
            String(val).toLowerCase().includes(q)
        );
    });
})

function formatRupiah(angka: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(angka || 0)
}
</script>

<template>
  <Head title="Dashboard TU & Umum" />
  <AppLayout>
    <div class="p-4 md:p-6 space-y-6 bg-slate-50/50 min-h-screen text-foreground">
      
      <div class="bg-card p-5 rounded-2xl shadow-sm border flex flex-col lg:flex-row gap-4 lg:items-center justify-between">
          <div>
              <h1 class="text-2xl font-bold tracking-tight text-primary">Dashboard TU & Umum</h1>
              <p class="text-sm text-muted-foreground">Monitoring SDM, Keuangan, Kehumasan, dan Kerjasama.</p>
          </div>
          <div class="flex flex-wrap md:flex-nowrap gap-3 w-full lg:w-auto">
              <div class="flex-1 md:w-auto">
                  <label class="text-[10px] uppercase font-bold text-muted-foreground block mb-1">Tanggal Data</label>
                  <input v-model="tanggal" type="date" class="w-full border rounded-lg px-3 py-2 text-sm bg-background shadow-sm focus:ring-2 focus:ring-primary/20" />
              </div>
              <div class="flex-1 md:w-auto">
                  <label class="text-[10px] uppercase font-bold text-muted-foreground block mb-1">Wilayah / UPT</label>
                  <select 
                      v-model="upt_id" 
                      :disabled="filters.is_upt_user"
                      class="w-full border rounded-lg px-3 py-2 text-sm font-semibold shadow-sm focus:ring-2 focus:ring-primary/20"
                      :class="filters.is_upt_user ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-background'"
                  >
                      <option value="" v-if="!filters.is_upt_user">KANWIL (Semua UPT)</option>
                      <option v-for="u in upts" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
                  </select>
              </div>
              <div class="flex-1 md:w-auto">
                  <label class="text-[10px] uppercase font-bold text-primary block mb-1">Fokus Modul</label>
                  <select v-model="modul" class="w-full border-0 rounded-lg px-3 py-2 text-sm bg-primary text-primary-foreground font-bold shadow-md cursor-pointer hover:bg-primary/90 transition-colors">
                      <option value="ringkasan">Ringkasan Total</option>
                      <option value="sdm">Data SDM</option>
                      <option value="keuangan">Realisasi Anggaran</option>
                      <option value="kehumasan">Kehumasan & Publikasi</option>
                      <option value="kerjasama">Kerjasama / MOU</option>
                  </select>
              </div>
          </div>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-card p-5 rounded-2xl shadow-sm border border-l-4 border-l-blue-500 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start">
                  <p class="text-xs text-muted-foreground font-bold uppercase">Kehadiran SDM</p>
                  <span class="bg-blue-100 text-blue-700 text-[10px] px-2 py-0.5 rounded-full font-bold">Hari Ini</span>
              </div>
              <h2 class="text-3xl font-black text-blue-600 mt-2">{{ highlight.total_pegawai_harian }} <span class="text-sm font-medium text-muted-foreground">Pegawai</span></h2>
          </div>
          <div class="bg-card p-5 rounded-2xl shadow-sm border border-l-4 border-l-emerald-500 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start">
                  <p class="text-xs text-muted-foreground font-bold uppercase">Serapan Anggaran</p>
                  <span class="bg-emerald-100 text-emerald-700 text-[10px] px-2 py-0.5 rounded-full font-bold">Keseluruhan</span>
              </div>
              <h2 class="text-3xl font-black text-emerald-600 mt-2">{{ highlight.persentase_anggaran }}% <span class="text-sm font-medium text-muted-foreground">Terserap</span></h2>
          </div>
          <div class="bg-card p-5 rounded-2xl shadow-sm border border-l-4 border-l-purple-500 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start">
                  <p class="text-xs text-muted-foreground font-bold uppercase">Publikasi Berita</p>
                  <span class="bg-purple-100 text-purple-700 text-[10px] px-2 py-0.5 rounded-full font-bold">Rilis Hari Ini</span>
              </div>
              <h2 class="text-3xl font-black text-purple-600 mt-2">{{ highlight.total_publikasi }} <span class="text-sm font-medium text-muted-foreground">Berita</span></h2>
          </div>
          <div class="bg-card p-5 rounded-2xl shadow-sm border border-l-4 border-l-orange-500 hover:shadow-md transition-shadow">
              <div class="flex justify-between items-start">
                  <p class="text-xs text-muted-foreground font-bold uppercase">Dokumen Kerjasama</p>
                  <span class="bg-orange-100 text-orange-700 text-[10px] px-2 py-0.5 rounded-full font-bold">MOU / PKS</span>
              </div>
              <h2 class="text-3xl font-black text-orange-600 mt-2">{{ highlight.mou_aktif }} <span class="text-sm font-medium text-muted-foreground">Selesai</span></h2>
              <p class="text-[10px] text-muted-foreground mt-1 font-medium">{{ highlight.mou_proses }} Dokumen dalam proses drafting/revisi</p>
          </div>
      </div>

      <div class="bg-card p-6 rounded-2xl shadow-sm border min-h-[400px]">
          
          <div v-if="modul === 'ringkasan'" class="space-y-6 animate-in fade-in duration-500">
              <div class="grid md:grid-cols-2 gap-6">
                  <div class="bg-purple-50/50 rounded-xl p-4 border border-purple-100">
                      <h3 class="font-bold text-purple-800 mb-3 flex items-center gap-2">📸 Giat Kehumasan & Publikasi Hari Ini</h3>
                      <div v-if="summary.publikasi.length" class="max-h-60 overflow-y-auto space-y-2 pr-2">
                          <div v-for="pub in summary.publikasi" :key="pub.id" class="bg-white p-3 rounded-lg border text-sm shadow-sm flex flex-col gap-1">
                              <p class="font-bold text-slate-800">{{ pub.nama_kegiatan }}</p>
                              <div class="flex justify-between items-center mt-1">
                                  <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded">{{ pub.media_publikasi }}</span>
                                  <span class="text-[10px] text-muted-foreground">{{ pub.upt?.name || 'UPT' }}</span>
                              </div>
                          </div>
                      </div>
                      <p v-else class="text-xs text-muted-foreground italic text-center py-4 bg-white rounded-lg border">Nihil rilis berita publikasi hari ini.</p>
                  </div>

                  <div class="bg-emerald-50/50 rounded-xl p-4 border border-emerald-100">
                      <h3 class="font-bold text-emerald-800 mb-3 flex items-center gap-2">💰 Snapshot Realisasi BAMA</h3>
                      <div v-if="summary.anggaran.length" class="max-h-60 overflow-y-auto space-y-2 pr-2">
                          <div v-for="ang in summary.anggaran" :key="ang.id" class="bg-white p-3 rounded-lg border text-sm shadow-sm flex flex-col">
                              <div class="flex justify-between items-start mb-2 border-b pb-2">
                                  <span class="font-bold text-slate-800 text-[11px]">{{ ang.upt?.name || 'UPT' }}</span>
                                  <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">Penyedia: {{ ang.bama_nama_penyedia || '-' }}</span>
                              </div>
                              <div class="flex justify-between text-xs mt-1">
                                  <span class="text-muted-foreground">Pagu:</span>
                                  <span class="font-bold">{{ formatRupiah(ang.bama_pagu_anggaran) }}</span>
                              </div>
                              <div class="flex justify-between text-xs mt-1">
                                  <span class="text-muted-foreground">Realisasi:</span>
                                  <span class="font-bold text-emerald-600">{{ formatRupiah(ang.bama_realisasi_anggaran) }}</span>
                              </div>
                          </div>
                      </div>
                      <p v-else class="text-xs text-muted-foreground italic text-center py-4 bg-white rounded-lg border">Data anggaran BAMA belum diinput hari ini.</p>
                  </div>
              </div>
          </div>

          <div v-else class="animate-in fade-in slide-in-from-bottom-2 duration-300">
              
              <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 border-b pb-4 gap-4">
                  <h2 class="text-lg font-bold capitalize text-primary">Detail Rincian Data: {{ modul }}</h2>
                  <div class="relative w-full sm:w-72">
                      <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"></span>
                      <input 
                          v-model="searchQuery" 
                          type="text" 
                          :placeholder="`Cari data ${modul}...`" 
                          class="w-full pl-9 pr-3 py-2 border rounded-xl text-sm shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all bg-slate-50 focus:bg-white"
                      />
                  </div>
              </div>
              
              <div v-if="!data_dinamis || !data_dinamis.length" class="text-center py-8 text-muted-foreground italic bg-slate-50 rounded-xl border">
                  Nihil data untuk modul {{ modul }} pada filter yang dipilih.
              </div>

              <div v-else class="rounded-xl border bg-white shadow-sm overflow-hidden flex flex-col">
                  <div class="max-h-[500px] overflow-y-auto overflow-x-auto relative w-full">
                      
                      <table v-if="modul === 'kerjasama'" class="w-full text-left border-collapse text-sm">
                          <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm border-b">
                              <tr class="text-slate-700 font-bold">
                                  <th class="p-4 w-12 text-center whitespace-nowrap">No</th>
                                  <th class="p-4">Detail Kerjasama / Mitra</th>
                                  <th class="p-4 whitespace-nowrap">Tingkat / Jenis</th>
                                  <th class="p-4 whitespace-nowrap">Masa Berlaku</th>
                                  <th class="p-4 text-center whitespace-nowrap">Status</th>
                                  <th class="p-4 text-center whitespace-nowrap">Aksi</th>
                              </tr>
                          </thead>
                          <tbody class="divide-y text-slate-600">
                              <tr v-if="!filteredData.length">
                                  <td colspan="6" class="p-8 text-center text-slate-400 italic">Pencarian "{{ searchQuery }}" tidak ditemukan.</td>
                              </tr>
                              <tr v-for="(item, index) in filteredData" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                                  <td class="p-4 text-center font-medium text-slate-400 align-top">{{ index + 1 }}</td>
                                  <td class="p-4 min-w-[250px]">
                                      <p class="font-bold text-slate-800 leading-snug mb-1.5 line-clamp-2" :title="item.judul_kerjasama">{{ item.judul_kerjasama }}</p>
                                      <div class="flex flex-col gap-0.5 text-xs text-muted-foreground">
                                          <span>Mitra: <strong class="text-slate-700">{{ item.instansi_kerjasama }}</strong></span>
                                          <span v-if="item.upt">Satker: {{ item.upt.name }}</span>
                                      </div>
                                  </td>
                                  <td class="p-4 whitespace-nowrap align-top">
                                      <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-800 mb-1">
                                          🏛️ {{ item.kategori_pemrakarsa }}
                                      </span>
                                      <p class="text-xs font-semibold text-slate-500">{{ item.jenis_kerjasama }}</p>
                                  </td>
                                  <td class="p-4 whitespace-nowrap text-xs font-medium align-top">
                                      <p class="text-emerald-600 mb-0.5">🟢 M: {{ item.masa_berlaku_mulai || '-' }}</p>
                                      <p class="text-rose-600">🔴 S: {{ item.masa_berlaku_selesai || '-' }}</p>
                                  </td>
                                  <td class="p-4 text-center whitespace-nowrap align-top">
                                      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold" :class="item.status_tahapan?.toLowerCase().includes('selesai') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'">
                                          {{ item.status_tahapan }}
                                      </span>
                                  </td>
                                  <td class="p-4 text-center whitespace-nowrap align-top">
                                      <a v-if="item.file_mou_pks" :href="item.file_mou_pks" target="_blank" class="inline-flex items-center justify-center bg-primary text-white text-[11px] font-bold px-3 py-1.5 rounded-lg hover:bg-primary/90 transition-all">📂 Berkas</a>
                                      <span v-else class="text-[11px] text-slate-400 bg-slate-50 px-2 py-1 rounded border">Kosong</span>
                                  </td>
                              </tr>
                          </tbody>
                      </table>

                      <table v-else-if="modul === 'sdm'" class="w-full text-left border-collapse text-sm">
                          <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm border-b">
                              <tr class="text-slate-700 font-bold">
                                  <th class="p-4 w-12 text-center whitespace-nowrap">No</th>
                                  <th class="p-4 min-w-[200px]">Nama Pegawai / NIP</th>
                                  <th class="p-4">Jabatan / Status</th>
                                  <th class="p-4 text-center whitespace-nowrap">Absensi</th>
                                  <th class="p-4">Keterangan</th>
                              </tr>
                          </thead>
                          <tbody class="divide-y text-slate-600">
                              <tr v-if="!filteredData.length">
                                  <td colspan="5" class="p-8 text-center text-slate-400 italic">Pencarian "{{ searchQuery }}" tidak ditemukan.</td>
                              </tr>
                              <tr v-for="(item, index) in filteredData" :key="item.id" class="hover:bg-slate-50/80">
                                  <td class="p-4 text-center text-slate-400">{{ index + 1 }}</td>
                                  <td class="p-4">
                                      <p class="font-bold text-slate-800">{{ item.nama_pegawai || item.nama }}</p>
                                      <span class="text-xs text-muted-foreground">NIP. {{ item.nip || '-' }}</span>
                                  </td>
                                  <td class="p-4">
                                      <p class="font-semibold text-xs">{{ item.jabatan || '-' }}</p>
                                      <span class="text-[10px] text-muted-foreground">🏢 {{ item.upt?.name }}</span>
                                  </td>
                                  <td class="p-4 text-center">
                                      <span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">{{ item.status_kehadiran || 'Hadir' }}</span>
                                  </td>
                                  <td class="p-4 text-xs text-slate-500">{{ item.keterangan || '-' }}</td>
                              </tr>
                          </tbody>
                      </table>

                      <table v-else-if="modul === 'keuangan'" class="w-full text-left border-collapse text-sm">
                          <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm border-b">
                              <tr class="text-slate-700 font-bold">
                                  <th class="p-4 w-12 text-center whitespace-nowrap">No</th>
                                  <th class="p-4">Satuan Kerja / UPT</th>
                                  <th class="p-4 text-right whitespace-nowrap">Pagu Anggaran</th>
                                  <th class="p-4 text-right whitespace-nowrap">Realisasi</th>
                                  <th class="p-4 text-center whitespace-nowrap">Sisa / Progress</th>
                              </tr>
                          </thead>
                          <tbody class="divide-y text-slate-600">
                              <tr v-if="!filteredData.length">
                                  <td colspan="5" class="p-8 text-center text-slate-400 italic">Pencarian "{{ searchQuery }}" tidak ditemukan.</td>
                              </tr>
                              <tr v-for="(item, index) in filteredData" :key="item.id" class="hover:bg-slate-50/80">
                                  <td class="p-4 text-center text-slate-400">{{ index + 1 }}</td>
                                  <td class="p-4 font-bold text-slate-800">{{ item.upt?.name || 'UPT' }}</td>
                                  <td class="p-4 text-right font-semibold">{{ formatRupiah(item.bama_pagu_anggaran || item.pagu) }}</td>
                                  <td class="p-4 text-right font-semibold text-emerald-600">{{ formatRupiah(item.bama_realisasi_anggaran || item.realisasi) }}</td>
                                  <td class="p-4 text-center">
                                      <span class="text-xs font-bold text-primary">{{ (((item.bama_realisasi_anggaran || 0) / (item.bama_pagu_anggaran || 1)) * 100).toFixed(1) }}%</span>
                                  </td>
                              </tr>
                          </tbody>
                      </table>

                      <table v-else-if="modul === 'kehumasan'" class="w-full text-left border-collapse text-sm">
                          <thead class="sticky top-0 z-10 bg-slate-50 shadow-sm border-b">
                              <tr class="text-slate-700 font-bold">
                                  <th class="p-4 w-12 text-center whitespace-nowrap">No</th>
                                  <th class="p-4 min-w-[250px]">Nama Kegiatan Publikasi</th>
                                  <th class="p-4 whitespace-nowrap">Media Rilis</th>
                                  <th class="p-4">Satker Pengirim</th>
                                  <th class="p-4 text-center whitespace-nowrap">Link Berita</th>
                              </tr>
                          </thead>
                          <tbody class="divide-y text-slate-600">
                              <tr v-if="!filteredData.length">
                                  <td colspan="5" class="p-8 text-center text-slate-400 italic">Pencarian "{{ searchQuery }}" tidak ditemukan.</td>
                              </tr>
                              <tr v-for="(item, index) in filteredData" :key="item.id" class="hover:bg-slate-50/80">
                                  <td class="p-4 text-center text-slate-400">{{ index + 1 }}</td>
                                  <td class="p-4 font-bold text-slate-800 line-clamp-2" :title="item.nama_kegiatan">{{ item.nama_kegiatan }}</td>
                                  <td class="p-4 whitespace-nowrap">
                                      <span class="px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-800">{{ item.media_publikasi }}</span>
                                  </td>
                                  <td class="p-4 text-xs">{{ item.upt?.name }}</td>
                                  <td class="p-4 text-center">
                                      <a v-if="item.url_berita || item.link" :href="item.url_berita || item.link" target="_blank" class="text-[11px] text-blue-600 font-bold bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors">Lihat 🔗</a>
                                      <span v-else class="text-[11px] text-slate-400 bg-slate-50 px-2 py-1 rounded border">Kosong</span>
                                  </td>
                              </tr>
                          </tbody>
                      </table>

                  </div>
              </div>
          </div>
      </div>
    </div>
  </AppLayout>
</template>