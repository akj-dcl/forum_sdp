<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

type JenisPemindahan = {
  id: number
  nama_pemindahan: string
}

const props = defineProps<{
  pemindahan: JenisPemindahan
}>()

const form = useForm({
  nama_pemindahan: props.pemindahan.nama_pemindahan ?? '',
})

function submit() {
  form.put(`/admin/jenis-pemindahans/${props.pemindahan.id}`)
}
</script>

<template>
  <Head title="Edit Jenis Tindak Pemindahan" />

  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Edit Jenis Tindak Pemindahan</h1>
          <p class="text-sm text-muted-foreground">Perbarui klasifikasi tindak pemindahan.</p>
        </div>

        <Link
          href="/admin/jenis-pemindahans"
          class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
        >
          Kembali
        </Link>
      </div>

      <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm">
        <form @submit.prevent="submit" class="p-6 space-y-6">
          <div class="grid gap-6 md:grid-cols-2">
            <div class="md:col-span-2 space-y-2">
              <label class="text-sm font-medium leading-none">Nama Pemindahan</label>
              <input
                v-model="form.nama_pemindahan"
                type="text"
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50"
              />
              <div v-if="form.errors.nama_pemindahan" class="text-sm font-medium text-destructive">{{ form.errors.nama_pemindahan }}</div>
            </div>
          </div>

          <div class="flex justify-end pt-4 border-t mt-6">
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-md bg-primary px-8 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 disabled:opacity-50"
            >
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>