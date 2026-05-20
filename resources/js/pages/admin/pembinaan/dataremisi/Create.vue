<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ upts: any[], jenis_remisis: any[] }>()

const form = useForm({
  upt_id: props.upts?.[0]?.id ? String(props.upts[0].id) : '',
  tanggal_input: new Date().toISOString().split('T')[0],
  detail_remisi: [
      { jenis_remisi_id: '', jumlah_diusulkan: 0, jumlah_remisi: 0, jumlah_selisih: '0', keterangan: '', sk_remisi: null, }
  ]
})

function addRow() {
    form.detail_remisi.push({ jenis_remisi_id: '', jumlah_diusulkan: 0, jumlah_remisi: 0, jumlah_selisih: '0', keterangan: '', sk_remisi: null as File | null, })
}

function removeRow(index: number) {
    if (form.detail_remisi.length > 1) {
        form.detail_remisi.splice(index, 1)
    }
}

function hitungSelisih(index: number) {
    const item = form.detail_remisi[index];
    const usulan = Number(item.jumlah_diusulkan) || 0;
    const setuju = Number(item.jumlah_remisi) || 0;
    item.jumlah_selisih = String(usulan - setuju);
}

function submit() {
  form.post('/admin/data-remisis', {
  forceFormData: true
})
}
</script>

<template>
  <Head title="Input Remisi Harian" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Input Data Remisi Harian</h1>
            <p class="text-sm text-muted-foreground">Catat pemberian beberapa jenis remisi sekaligus dalam satu hari.</p>
        </div>
        <Link href="/admin/data-remisis" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-bold">Tanggal Laporan (Input)</label>
                    <input v-model="form.tanggal_input" type="date" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-bold">Lokasi UPT</label>
                    <select v-model="form.upt_id" :disabled="upts.length === 1" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <option v-for="u in upts" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
            <div class="flex justify-between items-center mb-4 border-b border-border pb-4">
                <h2 class="text-lg font-bold text-primary">Daftar Remisi yang Diberikan</h2>
                <button type="button" @click="addRow" class="inline-flex items-center justify-center rounded-md bg-blue-100 text-blue-700 px-4 py-2 text-sm font-bold hover:bg-blue-200 transition-colors shadow-sm">
                    + Tambah Jenis Remisi
                </button>
            </div>

            <div class="space-y-6">
                <div v-for="(item, index) in form.detail_remisi" :key="index" class="p-4 rounded-xl bg-muted/30 border border-border relative shadow-sm">
                    
                    <button v-if="form.detail_remisi.length > 1" type="button" @click="removeRow(index)" class="absolute -top-3 -right-3 bg-red-500 text-white rounded-full w-7 h-7 flex items-center justify-center shadow hover:bg-red-600 transition-colors" title="Hapus baris ini">
                        &times;
                    </button>

                    <div class="grid md:grid-cols-5 gap-4 items-start">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-xs font-bold text-muted-foreground uppercase">Jenis Remisi</label>
                            <select v-model="item.jenis_remisi_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                <option value="">-- Pilih Jenis Remisi --</option>
                                <option v-for="jr in jenis_remisis" :key="jr.id" :value="String(jr.id)">{{ jr.nama_remisi }}</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-muted-foreground uppercase">Diusulkan</label>
                            <input v-model.number="item.jumlah_diusulkan" @input="hitungSelisih(index)" type="number" min="0" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-muted-foreground uppercase">Disetujui</label>
                            <input v-model.number="item.jumlah_remisi" @input="hitungSelisih(index)" type="number" min="0" required class="flex h-10 w-full rounded-md border border-emerald-500/50 bg-emerald-500/5 px-3 py-2 text-sm text-emerald-700 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-red-500 uppercase">Selisih</label>
                            <input v-model="item.jumlah_selisih" type="text" readonly class="flex h-10 w-full rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600 font-bold cursor-not-allowed focus:outline-none" />
                        </div>
                        
                        <div class="md:col-span-5 space-y-2 mt-2 pt-4 border-t border-border/50">
                            <label class="text-xs font-bold text-muted-foreground uppercase">Keterangan Tambahan</label>
                            <input v-model="item.keterangan" type="text" placeholder="Catatan tambahan (misal: 2 orang bebas langsung)..." class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
                        </div>
                        <div class="md:col-span-5 space-y-2">
    <label class="text-xs font-bold text-blue-600 uppercase">
        Upload SK Remisi (PDF)
    </label>

    <input
        type="file"
        accept="application/pdf"
        @input="item.sk_remisi = $event.target.files[0]"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
    />
</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-2 pb-8">
            <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-primary px-8 py-3 text-sm font-bold text-primary-foreground shadow-lg hover:bg-primary/90 disabled:opacity-50 transition-all">
                Simpan Seluruh Laporan
            </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>