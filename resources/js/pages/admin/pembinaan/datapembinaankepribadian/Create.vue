<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{
  upts: any[]
  jenis_kepribadians: any[]
}>()

const form = useForm({
  tanggal: new Date().toISOString().split('T')[0],
  upt_id: props.upts?.[0]?.id ? String(props.upts[0].id) : '',
  jenis_kepribadian_id: props.jenis_kepribadians?.[0]?.id ? String(props.jenis_kepribadians[0].id) : '',
  detail_lain_lain: '', // Penampung baru
  nama_kegiatan: '',
  pemateri: '',
  jumlah_peserta: '',
  hasil_kegiatan: '',
  rekomendasi_kegiatan: '',
  dokumentasi_kegiatan: [] as File[],
})

// SIHIR VUE: Mengecek apakah yang dipilih mengandung teks "lain"
const isLainLain = computed(() => {
  const selected = props.jenis_kepribadians.find(j => String(j.id) === form.jenis_kepribadian_id);
  if (!selected) return false;
  const nama = (selected.nama_kepribadian || selected.nama || '').toLowerCase();
  return nama.includes('lain');
});

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files) {
    form.dokumentasi_kegiatan = Array.from(target.files);
  }
}

// Ganti fungsi submit() milikmu yang ada di dalam <script setup> dengan ini es:
function submit() {
  if (!isLainLain.value) form.detail_lain_lain = '';
  
  form.post('/admin/data-pembinaan-kepribadians', {
    forceFormData: true, // 👈 INI KUNCINYA AGAR FILE TERUPLOAD
    preserveScroll: true,
    onError: (errors) => {
      console.log("Error Validasi:", errors);
      alert("Ada form yang belum diisi dengan benar! Cek pesan warna merah.");
    },
    onFinish: () => {
      form.processing = false; 
    }
  });
}
</script>

<template>
  <Head title="Tambah Kegiatan" />

  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Tambah Kegiatan Kepribadian</h1>
          <p class="text-sm text-muted-foreground">Input data kegiatan baru.</p>
        </div>
        <Link href="/admin/data-pembinaan-kepribadians" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground">Kembali</Link>
      </div>

      <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm">
        <form @submit.prevent="submit" class="p-6 space-y-6">
          <div class="grid gap-6 md:grid-cols-2">
            
            <div class="space-y-2">
              <label class="text-sm font-medium leading-none">Tanggal</label>
              <input v-model="form.tanggal" type="date" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background disabled:opacity-50" />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium leading-none">Lokasi UPT</label>
              <select v-model="form.upt_id" :disabled="upts.length === 1" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background disabled:opacity-50">
                <option v-for="u in upts" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
              </select>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium leading-none">Nama Kegiatan</label>
              <input v-model="form.nama_kegiatan" type="text" placeholder="Contoh: Pengajian Rutin" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background disabled:opacity-50" />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium leading-none">Jenis Kepribadian</label>
              <select v-model="form.jenis_kepribadian_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background disabled:opacity-50">
                <option v-for="jk in jenis_kepribadians" :key="jk.id" :value="String(jk.id)">{{ jk.nama_kepribadian || jk.nama }}</option>
              </select>
            </div>

            <div v-if="isLainLain" class="md:col-span-2 space-y-2 bg-blue-50/50 border border-blue-200 p-4 rounded-lg animate-in fade-in zoom-in duration-200">
              <label class="text-sm font-bold text-blue-700 leading-none">Deskripsikan Detail Jenis Lain-Lain</label>
              <input v-model="form.detail_lain_lain" type="text" placeholder="Jelaskan secara spesifik jenis kepribadiannya..." required class="flex h-10 w-full rounded-md border border-blue-300 bg-white px-3 py-2 text-sm focus:ring-blue-500" />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium leading-none">Pemateri / Pengisi Acara</label>
              <input v-model="form.pemateri" type="text" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background disabled:opacity-50" />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium leading-none">Jumlah Peserta</label>
              <input v-model="form.jumlah_peserta" type="number" min="1" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background disabled:opacity-50" />
            </div>

            <div class="md:col-span-2 space-y-2">
              <label class="text-sm font-medium leading-none">Hasil Kegiatan</label>
              <textarea v-model="form.hasil_kegiatan" rows="2" required class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background disabled:opacity-50"></textarea>
            </div>
            
            <div class="md:col-span-2 space-y-2">
              <label class="text-sm font-medium leading-none">Rekomendasi Lanjutan</label>
              <textarea v-model="form.rekomendasi_kegiatan" rows="2" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background disabled:opacity-50"></textarea>
            </div>

            <div class="md:col-span-2 space-y-2 bg-muted/30 p-4 rounded-lg border border-border">
              <label class="text-sm font-semibold leading-none">Upload Dokumentasi (Gambar & Video)</label>
              <p class="text-xs text-muted-foreground mb-2">Bisa pilih lebih dari 1 file sekaligus. Maksimal 5MB per file.</p>
              
              <input 
                type="file" 
                multiple 
                accept="image/*,video/mp4,video/quicktime"
                @change="handleFileChange" 
                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium hover:cursor-pointer" 
              />
              <div v-if="form.errors['dokumentasi_kegiatan']" class="text-sm font-medium text-destructive mt-1">
                {{ form.errors['dokumentasi_kegiatan'] }}
              </div>
            </div>

          </div>
          <div class="flex justify-end pt-4 border-t mt-6">
            <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-primary px-8 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 disabled:opacity-50">Simpan Kegiatan</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>