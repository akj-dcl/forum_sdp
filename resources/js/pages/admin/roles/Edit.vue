<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

type Role = {
  id: number
  name: string
}

const props = defineProps<{
  role: Role
  permissions: string[]
  rolePermissions: string[]
}>()

const form = useForm({
  name: props.role.name,
  permissions: props.rolePermissions
})

// Fungsi untuk mengelompokkan permissions berdasarkan kata pertama (sebelum tanda titik)
const groupedPermissions = computed(() => {
  const groups: Record<string, string[]> = {}
  props.permissions.forEach(permission => {
    const [groupName] = permission.split('.') // Ambil kata pertama sbg nama grup
    if (!groups[groupName]) {
      groups[groupName] = []
    }
    groups[groupName].push(permission)
  })
  return groups
})

function submit() {
  form.put(`/admin/roles/${props.role.id}`)
}
</script>

<template>
  <Head title="Edit Role" />

  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
      <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Edit Role</h1>
          <p class="text-sm text-muted-foreground">Buat peran pengguna baru dan atur hak aksesnya.</p>
        </div>

        <Link
          href="/admin/roles"
          class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
        >
          Kembali
        </Link>
      </div>

      <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm">
        <form @submit.prevent="submit" class="p-6 space-y-6">

          <div class="grid gap-6 md:grid-cols-2">

            <div class="md:col-span-2 space-y-2">
              <label class="text-sm font-medium leading-none">Nama Role</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Super Admin, Petugas P2U"
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50"
              />
              <div v-if="form.errors.name" class="text-sm font-medium text-destructive">{{ form.errors.name }}</div>
            </div>

            <div class="md:col-span-2 space-y-4 mt-4">
              <label class="text-sm font-medium leading-none border-b pb-2 block">Pilih Hak Akses (Permissions)</label>
              
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pt-2">
                <div v-for="(perms, groupName) in groupedPermissions" :key="groupName" class="space-y-3 bg-muted/30 p-4 rounded-lg border border-border">
                  <h3 class="font-semibold text-sm capitalize text-foreground border-b border-border pb-1 mb-2">
                    {{ groupName }}
                  </h3>
                  <div class="space-y-2">
                    <div v-for="permission in perms" :key="permission" class="flex items-center space-x-2">
                      <input
                        type="checkbox"
                        :id="permission"
                        :value="permission"
                        v-model="form.permissions"
                        class="h-4 w-4 rounded border-primary text-primary shadow focus:ring-primary cursor-pointer"
                      />
                      <label :for="permission" class="text-sm leading-none cursor-pointer text-muted-foreground hover:text-foreground">
                        {{ permission }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              
              <div v-if="form.errors.permissions" class="text-sm font-medium text-destructive">{{ form.errors.permissions }}</div>
            </div>
            </div>

          <div class="flex justify-end pt-4 border-t mt-6">
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-md bg-primary px-8 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 disabled:opacity-50"
            >
              Simpan Role
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>