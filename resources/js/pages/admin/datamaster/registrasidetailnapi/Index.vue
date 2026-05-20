<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'
import { can } from '@/lib/can'

type RegistrasiDetailNapi = {
  id: number
  nama_registrasidetailnapi: string
}
type PaginationLink = { url: string | null; label: string; active: boolean }

const props = defineProps<{
  registrasidetailnapis: {
    data: RegistrasiDetailNapi[]
    links: PaginationLink[]
    from: number
    to: number
    total: number
  }
  filters: {
    search?: string
  }
}>()

const page = usePage()
const search = ref(props.filters.search || '')

watch(
  search,
  debounce((newSearch: string) => {
    const params = new URLSearchParams();
    if (newSearch) params.append('search', newSearch);
    
    // Ganti window.location dengan router.get dari Inertia
    router.get(window.location.pathname, Object.fromEntries(params), {
      preserveState: true,
      preserveScroll: true,
      replace: true // Supaya riwayat back button tidak dipenuhi hasil pencarian tiap huruf
    });
  }, 300)
)

function destroyRegistrasiDetailNapi(id: number) {
  if (!confirm('Yakin mau hapus Jenis RegistrasiDetailNapi ini?')) return
  router.delete(`/admin/registrasi-detail-napis/${id}`)
}
</script>

<template>
  <Head title="Master Jenis RegistrasiDetailNapi" />

  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Master Jenis RegistrasiDetailNapi</h1>
          <p class="text-sm text-muted-foreground">Kelola klasifikasi jenis tindak registrasidetailnapi Warga Binaan Pemasyarakatan.</p>
        </div>

        <Link
          v-if="can('registrasi-detail-napis.create')"
          href="/admin/registrasi-detail-napis/create"
          class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:opacity-90 transition-colors"
        >
          + Tambah Data
        </Link>
      </div>

      <div
        v-if="page.props.flash?.success"
        class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600 dark:text-emerald-400"
      >
        {{ page.props.flash.success }}
      </div>

      <div class="grid gap-3 md:grid-cols-4">
        <input
          v-model="search"
          type="text"
          placeholder="Cari jenis registrasidetailnapi..."
          class="col-span-1 md:col-span-2 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        />
      </div>

      <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm overflow-hidden">
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm">
            <thead class="[&_tr]:border-b border-border">
              <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground w-3/4">Nama RegistrasiDetailNapi</th>
                <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Aksi</th>
              </tr>
            </thead>
            <tbody class="[&_tr:last-child]:border-0">
              <tr
                v-for="k in registrasidetailnapis.data"
                :key="k.id"
                class="border-b border-border transition-colors hover:bg-muted/50"
              >
                <td class="p-4 font-medium">{{ k.nama_registrasidetailnapi }}</td>
                <td class="p-4 text-right align-middle">
                  <div class="flex justify-end gap-2">
                    <Link
                      v-if="can('registrasi-detail-napis.edit')"
                      :href="`/admin/registrasi-detail-napis/${k.id}/edit`"
                      class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-transparent shadow-sm hover:bg-accent hover:text-accent-foreground h-8 px-3"
                    >
                      Edit
                    </Link>
                    <button
                      v-if="can('registrasi-detail-napis.delete')"
                      type="button"
                      @click="destroyRegistrasiDetailNapi(k.id)"
                      class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90 h-8 px-3"
                    >
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!registrasidetailnapis.data.length">
                <td colspan="2" class="p-8 text-center text-muted-foreground">
                  Tidak ada data Jenis Tindak RegistrasiDetailNapi ditemukan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="registrasidetailnapis.links.length > 3" class="flex items-center justify-between mt-2">
        <div class="text-sm text-muted-foreground">
          Menampilkan {{ registrasidetailnapis.from ?? 0 }} sampai {{ registrasidetailnapis.to ?? 0 }} dari {{ registrasidetailnapis.total }} data
        </div>
        <div class="flex gap-1">
          <template v-for="(link, key) in registrasidetailnapis.links" :key="key">
            <div v-if="link.url === null" class="px-3 py-1 text-sm text-muted-foreground border border-transparent" v-html="link.label" />
            <Link v-else :href="link.url" preserve-state preserve-scroll class="px-3 py-1 text-sm rounded-md border transition-colors" :class="link.active ? 'bg-primary text-primary-foreground border-primary' : 'border-border bg-background hover:bg-accent hover:text-accent-foreground'" v-html="link.label" />
          </template>
        </div>
      </div>
    </div>
  </AppLayout>
</template>