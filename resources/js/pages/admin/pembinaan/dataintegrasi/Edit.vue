<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{ dataintegrasi: any, upts: any[] }>()

const form = useForm({
  _method: 'put',
  upt_id: String(props.dataintegrasi.upt_id),
  tanggal_input: props.dataintegrasi.tanggal_input || '',

  tanggal_pelaksanaan_pb: props.dataintegrasi.tanggal_pelaksanaan_pb || '',
  jumlah_pb: props.dataintegrasi.jumlah_pb || '',
  sk_pb: null as File | null,
  dokumentasi_pb: [] as File[],

  tanggal_pelaksanaan_cb: props.dataintegrasi.tanggal_pelaksanaan_cb || '',
  jumlah_cb: props.dataintegrasi.jumlah_cb || '',
  sk_cb: null as File | null,
  dokumentasi_cb: [] as File[],

  tanggal_pelaksanaan_cmb: props.dataintegrasi.tanggal_pelaksanaan_cmb || '',
  jumlah_cmb: props.dataintegrasi.jumlah_cmb || '',
  sk_cmb: null as File | null,
  dokumentasi_cmb: [] as File[],

  tanggal_pelaksanaan_asimilasi: props.dataintegrasi.tanggal_pelaksanaan_asimilasi || '',
  jumlah_asimilasi: props.dataintegrasi.jumlah_asimilasi || '',
  sk_asimilasi: null as File | null,
  dokumentasi_asimilasi: [] as File[],

  // 👇 TAMBAHAN BARU
  tanggal_pelaksanaan_bebas_murni: props.dataintegrasi.tanggal_pelaksanaan_bebas_murni || '',
  jumlah_bebas_murni: props.dataintegrasi.jumlah_bebas_murni || '',
  sk_bebas_murni: null as File | null,
  dokumentasi_bebas_murni: [] as File[],

  tanggal_pelaksanaan_cmk: props.dataintegrasi.tanggal_pelaksanaan_cmk || '',
  jumlah_cmk: props.dataintegrasi.jumlah_cmk || '',
  sk_cmk: null as File | null,
  dokumentasi_cmk: [] as File[],
})

function handleMultipleFiles(event: Event, key: string) {
  const target = event.target as HTMLInputElement
  if (target.files) form[`dokumentasi_${key}`] = Array.from(target.files)
}

function submit() {
  form.post(`/admin/data-integrasis/${props.dataintegrasi.id}`, { forceFormData: true, preserveScroll: true })
}
</script>

<template>
  <Head title="Edit Data Integrasi" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Edit Data Integrasi WBP</h1>
        <Link href="/admin/data-integrasis" class="border px-4 py-2 rounded-md hover:bg-accent text-sm">Kembali</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="rounded-xl border bg-card p-6 shadow-sm grid md:grid-cols-2 gap-6">
          <div class="space-y-2"><label class="text-sm font-bold">Tanggal Laporan</label><input v-model="form.tanggal_input" type="date" required class="w-full h-10 border rounded-md px-3 bg-background" /></div>
          <div class="space-y-2">
            <label class="text-sm font-bold">Lokasi UPT</label>
            <select v-model="form.upt_id" required class="w-full h-10 border rounded-md px-3 bg-background">
              <option v-for="u in upts" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
            </select>
          </div>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="j in [
              { key: 'pb', label: 'Pembebasan Bersyarat (PB)' },
              { key: 'cb', label: 'Cuti Bersyarat (CB)' },
              { key: 'cmb', label: 'Cuti Menjelang Bebas (CMB)' },
              { key: 'asimilasi', label: 'Asimilasi' },
              { key: 'bebas_murni', label: 'Bebas Murni' },
              { key: 'cmk', label: 'Cuti Menjelang Keluarga (CMK)' }
            ]" :key="j.key" class="rounded-xl border bg-card p-5 shadow-sm"
          >
            <h3 class="font-bold text-base mb-4 text-primary border-b pb-2">{{ j.label }}</h3>
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div class="space-y-2">
                <label class="text-xs font-medium">Tgl Pelaksanaan</label>
                <input v-model="form[`tanggal_pelaksanaan_${j.key}`]" type="date" :required="form[`jumlah_${j.key}`] > 0" class="w-full h-9 text-sm border rounded px-2 bg-background" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-medium">Jumlah Orang</label>
                <input v-model="form[`jumlah_${j.key}`]" type="number" min="0" required class="w-full h-9 text-sm border rounded px-2 bg-background" />
              </div>
            </div>

            <div class="space-y-3 bg-muted/30 p-3 rounded-lg border">
              <div>
                <label class="text-xs font-bold text-blue-600 block mb-1">Ganti SK (PDF)</label>
                <input type="file" accept="application/pdf" @input="form[`sk_${j.key}`] = $event.target.files[0]" class="text-xs w-full" />
                <p class="text-[10px] mt-1 text-muted-foreground">Kosongkan jika tidak diganti.</p>
              </div>
              <div class="pt-2 border-t">
                <label class="text-xs font-bold text-primary block mb-1">Ganti Dokumentasi</label>
                <input type="file" multiple accept="image/*" @change="handleMultipleFiles($event, j.key)" class="text-xs w-full" />
                <p class="text-[10px] mt-1 text-muted-foreground">Kosongkan jika tidak diganti.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="text-right pt-4">
          <button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground px-8 py-3 rounded-md font-bold shadow hover:opacity-90">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>