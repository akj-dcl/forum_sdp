<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    roles: { id: number, name: string }[];
    kanwils: { id: number, name: string }[];
    upts: { id: number, name: string, kanwil_id: number }[];
    jenisGolongans: { id: number, nama_golongan: string }[];
}>();

// Filtered UPT list based on selected Kanwil
const filteredUpts = computed(() => {
    if (!form.kanwil_id) return [];
    return props.upts.filter(upt => upt.kanwil_id === form.kanwil_id);
});

const form = useForm({
    name: '',
    nip: '',
    jabatan: '',
    roles: [] as string[],
    kanwil_id: '' as string | number,
    upt_id: '' as string | number,
    jenis_golongan_id: '' as string | number,
    password: '',
});

const isOpen = ref(false);
const searchQuery = ref('');

const filteredRoles = computed(() => {
    if (!searchQuery.value) return props.roles;
    const query = searchQuery.value.toLowerCase();
    return props.roles.filter(role => role.name.toLowerCase().includes(query));
});

const toggleRole = (roleName: string) => {
    const index = form.roles.indexOf(roleName);
    if (index === -1) {
        form.roles.push(roleName);
    } else {
        form.roles.splice(index, 1);
    }
};

const removeRole = (roleName: string) => {
    const index = form.roles.indexOf(roleName);
    if (index !== -1) {
        form.roles.splice(index, 1);
    }
};

// Watch Kanwil change to reset selected UPT
watch(() => form.kanwil_id, () => {
    form.upt_id = '';
});

const submit = () => {
    form.post('/admin/data-akun');
};
</script>

<template>
    <Head title="Tambah Akun Pegawai" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 bg-background text-foreground">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">Tambah Akun Pegawai</h1>
                    <p class="text-sm text-muted-foreground">Buat akun akses sistem untuk Operator atau Admin.</p>
                </div>
                <Link href="/admin/data-akun" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground">Kembali</Link>
            </div>

            <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">Nama Lengkap Pegawai *</label>
                            <input v-model="form.name" type="text" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50" required />
                            <div v-if="form.errors.name" class="text-sm font-medium text-destructive">{{ form.errors.name }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">NIP (Username Login) *</label>
                            <input v-model="form.nip" type="text" placeholder="Masukkan 18 digit NIP" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50" required />
                            <div v-if="form.errors.nip" class="text-sm font-medium text-destructive">{{ form.errors.nip }}</div>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-medium leading-none">Jabatan *</label>
                            <input v-model="form.jabatan" type="text" placeholder="Contoh: Pranata Komputer Ahli Pertama" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50" required />
                            <div v-if="form.errors.jabatan" class="text-sm font-medium text-destructive">{{ form.errors.jabatan }}</div>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium leading-none">Hak Akses (Role) *</label>
                            
                            <!-- Custom Searchable Multiple Select Dropdown -->
                            <div class="relative">
                                <button type="button" @click="isOpen = !isOpen" class="flex min-h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring select-none text-left">
                                    <div class="flex flex-wrap gap-1 min-h-[20px] items-center">
                                        <span v-if="form.roles.length === 0" class="text-muted-foreground">-- Pilih Satu atau Lebih Role --</span>
                                        <span v-for="rName in form.roles" :key="rName" class="inline-flex items-center gap-1 rounded bg-accent px-2 py-0.5 text-xs text-accent-foreground font-semibold">
                                            {{ rName }}
                                            <span @click.stop="removeRole(rName)" class="text-[10px] cursor-pointer hover:text-destructive ml-1">×</span>
                                        </span>
                                    </div>
                                    <span class="material-symbols-outlined text-[16px] text-muted-foreground ml-2">keyboard_arrow_down</span>
                                </button>

                                <div v-if="isOpen" class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-border bg-card p-2 text-card-foreground shadow-md">
                                    <input v-model="searchQuery" type="text" placeholder="Cari role..." class="mb-2 flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" />
                                    <div class="space-y-1">
                                        <div v-for="role in filteredRoles" :key="role.id" class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-accent cursor-pointer select-none" @click="toggleRole(role.name)">
                                            <input type="checkbox" :checked="form.roles.includes(role.name)" @click.stop="toggleRole(role.name)" class="rounded border-input text-primary focus:ring-primary h-4 w-4" />
                                            <span class="text-sm font-medium">{{ role.name }}</span>
                                        </div>
                                        <div v-if="filteredRoles.length === 0" class="text-xs text-muted-foreground p-2 text-center">Tidak ada role yang cocok.</div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.roles" class="text-sm font-medium text-destructive">{{ form.errors.roles }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">Pangkat / Golongan</label>
                            <select v-model="form.jenis_golongan_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50">
                                <option value="">-- Pilih Pangkat/Golongan --</option>
                                <option v-for="golongan in jenisGolongans" :key="golongan.id" :value="golongan.id">{{ golongan.nama_golongan }}</option>
                            </select>
                            <div v-if="form.errors.jenis_golongan_id" class="text-sm font-medium text-destructive">{{ form.errors.jenis_golongan_id }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">Kantor Wilayah (Kanwil)</label>
                            <select v-model="form.kanwil_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50">
                                <option value="">-- Pilih Kantor Wilayah --</option>
                                <option v-for="kanwil in kanwils" :key="kanwil.id" :value="kanwil.id">{{ kanwil.name }}</option>
                            </select>
                            <div v-if="form.errors.kanwil_id" class="text-sm font-medium text-destructive">{{ form.errors.kanwil_id }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">Penempatan UPT (Lapas/Rutan)</label>
                            <select v-model="form.upt_id" :disabled="!form.kanwil_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50">
                                <option value="">-- Kantor Wilayah / Bebas UPT --</option>
                                <option v-for="upt in filteredUpts" :key="upt.id" :value="upt.id">{{ upt.name }}</option>
                            </select>
                            <div v-if="form.errors.upt_id" class="text-sm font-medium text-destructive">{{ form.errors.upt_id }}</div>
                            <p class="text-xs text-muted-foreground mt-1">Pilih Kanwil terlebih dahulu. Biarkan kosong jika ini akun Kanwil/Super Admin.</p>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-medium leading-none">Password Login *</label>
                            <input v-model="form.password" type="password" class="flex h-10 w-full md:w-1/2 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50" required />
                            <div v-if="form.errors.password" class="text-sm font-medium text-destructive">{{ form.errors.password }}</div>
                        </div>

                    </div>
                    <div class="flex justify-end pt-4 border-t mt-6">
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-primary px-8 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 disabled:opacity-50">
                            Simpan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>