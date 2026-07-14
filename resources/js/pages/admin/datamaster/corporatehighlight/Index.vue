<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

type CorporateHighlight = {
  id: number
  title: string
  description: string
  link: string | null
}
type PaginationLink = { url: string | null; label: string; active: boolean }

const props = defineProps<{
  highlights: {
    data: CorporateHighlight[]
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
    const params = new URLSearchParams()
    if (newSearch) params.append('search', newSearch)
    
    router.get(window.location.pathname, Object.fromEntries(params), {
      preserveState: true,
      preserveScroll: true,
      replace: true
    })
  }, 300)
)

const showModal = ref(false)
const modalTitle = ref('')
const isEditing = ref(false)
const editingId = ref<number | null>(null)

const form = useForm({
  title: '',
  description: '',
  link: ''
})

const openCreateModal = () => {
  modalTitle.value = 'Tambah Highlight Baru'
  isEditing.value = false
  editingId.value = null
  form.reset()
  showModal.value = true
}

const openEditModal = (h: CorporateHighlight) => {
  modalTitle.value = 'Edit Highlight'
  isEditing.value = true
  editingId.value = h.id
  form.title = h.title
  form.description = h.description
  form.link = h.link || ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  form.reset()
}

const submitForm = () => {
  if (isEditing.value && editingId.value) {
    form.put(`/admin/corporate-highlights/${editingId.value}`, {
      onSuccess: () => closeModal()
    })
  } else {
    form.post('/admin/corporate-highlights', {
      onSuccess: () => closeModal()
    })
  }
}

const destroyHighlight = (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus highlight ini?')) return
  router.delete(`/admin/corporate-highlights/${id}`)
}
</script>

<template>
  <Head title="Manajemen Corporate Highlights" />

  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 bg-surface text-on-surface">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-on-surface">Manajemen Corporate Highlights</h1>
          <p class="text-sm text-on-surface-variant">Kelola pengumuman penting (highlights) yang muncul di sidebar dashboard.</p>
        </div>

        <button
          @click="openCreateModal"
          class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-sm hover:bg-on-primary-fixed-variant transition-colors border-none cursor-pointer"
        >
          + Tambah Highlight
        </button>
      </div>

      <div
        v-if="page.props.flash?.success"
        class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600 dark:text-emerald-400 select-none"
      >
        {{ page.props.flash.success }}
      </div>

      <div class="grid gap-3 md:grid-cols-4">
        <div class="relative col-span-1 md:col-span-2">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
          <input
            v-model="search"
            type="text"
            placeholder="Cari highlight..."
            class="flex h-10 w-full rounded-lg border border-outline bg-surface-container-low pl-10 pr-3 py-2 text-sm text-on-surface focus:outline-none focus:border-primary"
          />
        </div>
      </div>

      <div class="rounded-xl border border-outline-variant bg-surface-container-lowest text-on-surface shadow-sm overflow-hidden">
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm border-collapse">
            <thead class="bg-surface-container border-b border-outline-variant">
              <tr>
                <th class="h-12 px-4 text-left align-middle font-semibold text-on-surface-variant">Judul Highlight</th>
                <th class="h-12 px-4 text-left align-middle font-semibold text-on-surface-variant">Keterangan / Info</th>
                <th class="h-12 px-4 text-left align-middle font-semibold text-on-surface-variant">Link (Tautan)</th>
                <th class="h-12 px-4 text-right align-middle font-semibold text-on-surface-variant">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
              <tr
                v-for="h in highlights.data"
                :key="h.id"
                class="hover:bg-surface-container-low transition-colors"
              >
                <td class="p-4 align-middle font-bold text-on-surface">
                  {{ h.title }}
                </td>
                <td class="p-4 align-middle text-on-surface-variant">
                  {{ h.description }}
                </td>
                <td class="p-4 align-middle text-xs text-primary truncate max-w-[200px]">
                  {{ h.link || '-' }}
                </td>
                <td class="p-4 text-right align-middle">
                  <div class="flex justify-end gap-2">
                    <button
                      @click="openEditModal(h)"
                      class="inline-flex items-center justify-center rounded-lg border border-outline bg-transparent hover:bg-surface-container-high transition-colors h-9 px-4 text-xs font-semibold text-on-surface cursor-pointer"
                    >
                      Edit
                    </button>
                    <button
                      type="button"
                      @click="destroyHighlight(h.id)"
                      class="inline-flex items-center justify-center rounded-lg bg-error hover:bg-red-700 text-on-error transition-colors h-9 px-4 text-xs font-semibold border-none cursor-pointer"
                    >
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!highlights.data.length">
                <td colspan="4" class="p-8 text-center text-on-surface-variant select-none">
                  Tidak ada corporate highlights ditemukan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="highlights.links.length > 3" class="flex items-center justify-between mt-2 select-none">
        <div class="text-sm text-on-surface-variant">
          Menampilkan {{ highlights.from ?? 0 }} sampai {{ highlights.to ?? 0 }} dari {{ highlights.total }} data
        </div>
        <div class="flex gap-1">
          <template v-for="(link, key) in highlights.links" :key="key">
            <div
              v-if="link.url === null"
              class="px-3 py-1.5 text-sm text-outline border border-transparent"
              v-html="link.label"
            />
            <Link
              v-else
              :href="link.url"
              preserve-state 
              preserve-scroll
              class="px-3 py-1.5 text-sm rounded-lg border transition-colors cursor-pointer"
              :class="link.active
                ? 'bg-primary text-on-primary border-primary font-semibold'
                : 'border-outline bg-surface-container hover:bg-surface-container-high text-on-surface'"
              v-html="link.label"
            />
          </template>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 select-none">
      <div class="w-full max-w-md bg-surface-container-lowest border border-outline-variant rounded-xl shadow-xl overflow-hidden flex flex-col">
        <div class="bg-primary text-on-primary px-5 py-4 flex items-center justify-between">
          <h3 class="font-bold font-headline-sm">{{ modalTitle }}</h3>
          <button @click="closeModal" class="text-on-primary hover:bg-white/10 rounded p-1 cursor-pointer border-none bg-transparent flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="p-5 flex flex-col gap-4">
          <div class="flex flex-col gap-1.5">
            <label class="font-label-md text-label-md text-on-surface font-semibold">Judul Highlight</label>
            <input
              v-model="form.title"
              type="text"
              required
              class="h-10 px-3 border border-outline rounded-lg bg-surface-container-low text-on-surface focus:outline-none focus:border-primary text-sm"
              placeholder="Contoh: Rapat Koordinasi Kanwil"
            />
            <span v-if="form.errors.title" class="text-xs text-error mt-0.5">{{ form.errors.title }}</span>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="font-label-md text-label-md text-on-surface font-semibold">Keterangan / Info</label>
            <input
              v-model="form.description"
              type="text"
              required
              class="h-10 px-3 border border-outline rounded-lg bg-surface-container-low text-on-surface focus:outline-none focus:border-primary text-sm"
              placeholder="Contoh: Mulai 12 Juli • Wajib bagi Ka. UPT"
            />
            <span v-if="form.errors.description" class="text-xs text-error mt-0.5">{{ form.errors.description }}</span>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="font-label-md text-label-md text-on-surface font-semibold">Link Tautan (Opsional)</label>
            <input
              v-model="form.link"
              type="text"
              class="h-10 px-3 border border-outline rounded-lg bg-surface-container-low text-on-surface focus:outline-none focus:border-primary text-sm"
              placeholder="Contoh: https://zoom.us/j/123..."
            />
            <span v-if="form.errors.link" class="text-xs text-error mt-0.5">{{ form.errors.link }}</span>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-outline-variant mt-2">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-outline text-on-surface rounded-lg text-sm font-semibold cursor-pointer hover:bg-surface-container-high transition-colors bg-transparent"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 bg-primary text-on-primary rounded-lg text-sm font-semibold border-none cursor-pointer hover:bg-on-primary-fixed-variant transition-colors"
            >
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
