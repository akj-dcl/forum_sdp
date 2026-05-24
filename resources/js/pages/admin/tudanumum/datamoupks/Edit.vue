<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{ datamoupks: any, upts: any[], isUptUser: boolean }>()

// FORM 1: Edit Info Dasar
const formInfo = useForm({
    _method: 'put',
    is_update_info: 'yes',
    judul_kerjasama: props.datamoupks.judul_kerjasama,
    jenis_kerjasama: props.datamoupks.jenis_kerjasama,
    instansi_kerjasama: props.datamoupks.instansi_kerjasama,
})

// FORM 2: Tambah Tahapan
const formTahapan = useForm({
    _method: 'put',
    tahapan_baru: '',
    catatan_baru: '',
    file_draft_baru: '',
})

// FORM 3: Finalisasi
const formFinal = useForm({
    _method: 'put',
    is_finalisasi: 'yes',
    masa_berlaku_mulai: '',
    masa_berlaku_selesai: '',
    file_mou_pks: '',
    dokumentasi_penandatanganan: '',
})

// FORM 4: Update Final
const formUpdateFinal = useForm({
    _method: 'put',
    is_update_final: 'yes',

    masa_berlaku_mulai:
        props.datamoupks.masa_berlaku_mulai
            ? props.datamoupks.masa_berlaku_mulai.split('T')[0]
            : '',

    masa_berlaku_selesai:
        props.datamoupks.masa_berlaku_selesai
            ? props.datamoupks.masa_berlaku_selesai.split('T')[0]
            : '',

    file_mou_pks: props.datamoupks.file_mou_pks || '',
    dokumentasi_penandatanganan:
        props.datamoupks.dokumentasi_penandatanganan || '',
})

function safeParseJson(data: any) {
    if (!data) return [];
    if (typeof data === 'string') {
        try {
            return JSON.parse(data);
        } catch (e) {
            return [];
        }
    }
    return data;
}

const riwayat = computed(() => safeParseJson(props.datamoupks.riwayat_tahapan));

function submitInfo() {
    formInfo.post(`/admin/data-mou-pks/${props.datamoupks.id}`, {
        preserveScroll: true
    })
}

function submitTahapan() {
    formTahapan.post(`/admin/data-mou-pks/${props.datamoupks.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            formTahapan.reset('tahapan_baru', 'catatan_baru', 'file_draft_baru')
        }
    })
}

function submitFinal() {
    formFinal.post(`/admin/data-mou-pks/${props.datamoupks.id}`)
}

function submitUpdateFinal() {
    formUpdateFinal.post(`/admin/data-mou-pks/${props.datamoupks.id}`, {
        preserveScroll: true
    })
}
</script>

<template>
  <Head title="Kelola MOU" />

  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">

      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-primary">
            Kelola Data & Alur MOU PKS
          </h1>
          <p class="text-sm text-muted-foreground">
            {{ datamoupks.judul_kerjasama }}
          </p>
        </div>

        <Link
          href="/admin/data-mou-pks"
          class="border px-4 py-2 rounded-md hover:bg-accent text-sm font-medium"
        >
          Kembali
        </Link>
      </div>

      <!-- FORM INFO -->
      <form
        @submit.prevent="submitInfo"
        class="rounded-xl border bg-card p-5 shadow-sm mb-2"
      >
        <div class="flex justify-between items-center border-b pb-2 mb-4">
          <h3 class="font-bold text-primary">
            A. IDENTITAS KERJASAMA
          </h3>

          <button
            type="submit"
            :disabled="formInfo.processing"
            class="bg-blue-100 text-blue-700 px-4 py-1.5 rounded text-xs font-bold hover:bg-blue-200"
          >
            Simpan Perubahan Info
          </button>
        </div>

        <div class="grid md:grid-cols-3 gap-4">

          <div class="space-y-1">
            <label class="text-xs font-bold text-muted-foreground">
              Judul Kerjasama
            </label>

            <input
              v-model="formInfo.judul_kerjasama"
              type="text"
              required
              class="w-full h-9 border rounded px-3 text-sm"
            />
          </div>

          <div class="space-y-1">
            <label class="text-xs font-bold text-muted-foreground">
              Jenis Kerjasama
            </label>

            <select
              v-model="formInfo.jenis_kerjasama"
              required
              class="w-full h-9 border rounded px-3 text-sm bg-white"
            >
              <option value="PERJANJIAN KERJASAMA (PKS)">
                PERJANJIAN KERJASAMA (PKS)
              </option>

              <option value="NOTA KESEPAHAMAN">
                NOTA KESEPAHAMAN
              </option>
            </select>
          </div>

          <div class="space-y-1">
            <label class="text-xs font-bold text-muted-foreground">
              Instansi Mitra
            </label>

            <input
              v-model="formInfo.instansi_kerjasama"
              type="text"
              required
              class="w-full h-9 border rounded px-3 text-sm"
            />
          </div>

        </div>
      </form>

      <div class="grid lg:grid-cols-2 gap-6">

        <!-- RIWAYAT -->
        <div class="space-y-4">

          <h2 class="text-lg font-bold border-b pb-2">
            B. Riwayat Draft & Revisi
          </h2>

          <div
            v-if="riwayat.length === 0"
            class="text-muted-foreground italic text-sm"
          >
            Belum ada riwayat.
          </div>

          <div
            class="relative border-l-2 border-primary ml-3 space-y-6 pb-4"
          >

            <div
              v-for="(r, idx) in riwayat"
              :key="idx"
              class="relative pl-6"
            >

              <div
                class="absolute -left-[9px] top-1 h-4 w-4 rounded-full bg-primary border-4 border-background shadow"
              ></div>

              <div
                class="bg-card border rounded-lg p-4 shadow-sm"
              >

                <div class="flex justify-between items-start mb-2">

                  <div>
                    <span
                      class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase"
                    >
                      {{ r.tahapan }}
                    </span>

                    <p class="text-xs text-muted-foreground mt-1">
                      {{ r.tanggal }}
                    </p>
                  </div>

                  <a
                    :href="r.file"
                    target="_blank"
                    class="text-[10px] bg-blue-600 text-white px-3 py-1.5 rounded shadow hover:bg-blue-700"
                  >
                    Buka Link
                  </a>

                </div>

                <div
                  class="bg-muted/30 p-2 rounded text-sm italic border-l-2 border-blue-300"
                >
                  "{{ r.catatan }}"
                </div>

              </div>

            </div>

          </div>

        </div>

        <!-- FORM KANAN -->
        <div class="space-y-6">

          <!-- JIKA BELUM SELESAI -->
          <div
            v-if="!datamoupks.status_tahapan.includes('Selesai')"
            class="space-y-6"
          >

            <!-- TAHAPAN -->
            <form
              @submit.prevent="submitTahapan"
              class="rounded-xl border border-orange-200 bg-orange-50/50 p-5 shadow-sm space-y-4"
            >

              <h3 class="font-bold text-orange-800 border-b border-orange-200 pb-2">
                ➕ Tambah Tahapan
              </h3>

              <div class="space-y-2">
                <label class="text-xs font-bold uppercase">
                  Tahapan Baru
                </label>

                <select
                  v-model="formTahapan.tahapan_baru"
                  required
                  class="w-full h-9 border rounded px-3 text-sm bg-white"
                >
                  <option value="">-- Pilih Tahapan --</option>

                  <template v-if="isUptUser">
                      <option value="Perbaikan (Dari UPT)">Perbaikan (Dari UPT)</option>
                      <option value="Draft Final (Menunggu TTD)">Draft Final (Menunggu TTD)</option>
                  </template>

                  <template v-else>
                      <option value="Revisi (Dari Kanwil)">Revisi (Dari Kanwil)</option>
                      <option value="Perbaikan (Dari UPT)">Perbaikan (Dari UPT)</option>
                      <option value="Validasi (Oleh Kanwil)">Validasi (Oleh Kanwil)</option>
                      <option value="Draft Final (Menunggu TTD)">Draft Final (Menunggu TTD)</option>
                  </template>
                </select>
              </div>

              <div class="space-y-2">
                <label class="text-xs font-bold uppercase">
                  Link Draft
                </label>

                <input
                  v-model="formTahapan.file_draft_baru"
                  type="url"
                  placeholder="https://drive.google.com/..."
                  required
                  class="w-full border rounded px-3 py-2 text-sm bg-white"
                />
              </div>

              <div class="space-y-2">
                <label class="text-xs font-bold uppercase">
                  Catatan
                </label>

                <textarea
                  v-model="formTahapan.catatan_baru"
                  rows="2"
                  required
                  class="w-full border rounded px-3 py-2 text-sm bg-white"
                ></textarea>
              </div>

              <button
                type="submit"
                :disabled="formTahapan.processing"
                class="w-full bg-orange-600 text-white font-bold py-2 rounded"
              >
                Kirim Tahapan
              </button>

            </form>

            <!-- FINALISASI -->
            <form
              @submit.prevent="submitFinal"
              class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm space-y-4"
            >

              <h3 class="font-bold text-emerald-800 border-b border-emerald-200 pb-2">
                ✅ FINALISASI
              </h3>

              <div class="grid grid-cols-2 gap-4">

                <div class="space-y-2">
                  <label class="text-xs font-bold uppercase">
                    Berlaku Mulai
                  </label>

                  <input
                    v-model="formFinal.masa_berlaku_mulai"
                    type="date"
                    required
                    class="w-full h-9 border rounded px-2 text-sm bg-white"
                  />
                </div>

                <div class="space-y-2">
                  <label class="text-xs font-bold uppercase">
                    Berlaku Sampai
                  </label>

                  <input
                    v-model="formFinal.masa_berlaku_selesai"
                    type="date"
                    required
                    class="w-full h-9 border rounded px-2 text-sm bg-white"
                  />
                </div>

              </div>

              <div class="space-y-2">
                <label class="text-xs font-bold">
                  Link Dokumen MOU/PKS
                </label>

                <input
                  v-model="formFinal.file_mou_pks"
                  type="url"
                  placeholder="https://..."
                  required
                  class="w-full border rounded px-3 py-2 text-sm bg-white"
                />
              </div>

              <div class="space-y-2">
                <label class="text-xs font-bold">
                  Link Dokumentasi
                </label>

                <input
                  v-model="formFinal.dokumentasi_penandatanganan"
                  type="url"
                  placeholder="https://..."
                  class="w-full border rounded px-3 py-2 text-sm bg-white"
                />
              </div>

              <button
                type="submit"
                :disabled="formFinal.processing"
                class="w-full bg-emerald-600 text-white font-bold py-2 rounded"
              >
                Finalisasi MOU
              </button>

            </form>

          </div>

          <!-- SUDAH SELESAI -->
          <div v-else class="space-y-6">

            <form
              @submit.prevent="submitUpdateFinal"
              class="rounded-xl border bg-card p-5 shadow-sm space-y-4"
            >

              <h3 class="font-bold text-primary border-b pb-2">
                Edit Dokumen Final
              </h3>

              <div class="grid grid-cols-2 gap-4">

                <div class="space-y-2">
                  <label class="text-xs font-bold">
                    Masa Berlaku Mulai
                  </label>

                  <input
                    v-model="formUpdateFinal.masa_berlaku_mulai"
                    type="date"
                    required
                    class="w-full h-9 border rounded px-2 text-sm bg-white"
                  />
                </div>

                <div class="space-y-2">
                  <label class="text-xs font-bold">
                    Masa Berlaku Selesai
                  </label>

                  <input
                    v-model="formUpdateFinal.masa_berlaku_selesai"
                    type="date"
                    required
                    class="w-full h-9 border rounded px-2 text-sm bg-white"
                  />
                </div>

              </div>

              <div class="space-y-2">
                <label class="text-xs font-bold">
                  Link Dokumen Final
                </label>

                <input
                  v-model="formUpdateFinal.file_mou_pks"
                  type="url"
                  class="w-full border rounded px-3 py-2 text-sm bg-white"
                />
              </div>

              <div class="space-y-2">
                <label class="text-xs font-bold">
                  Link Dokumentasi
                </label>

                <input
                  v-model="formUpdateFinal.dokumentasi_penandatanganan"
                  type="url"
                  class="w-full border rounded px-3 py-2 text-sm bg-white"
                />
              </div>

              <div class="text-right">
                <button
                  type="submit"
                  :disabled="formUpdateFinal.processing"
                  class="bg-blue-600 text-white font-bold px-6 py-2 rounded"
                >
                  Simpan Perubahan
                </button>
              </div>

            </form>

          </div>

        </div>

      </div>

    </div>
  </AppLayout>
</template>