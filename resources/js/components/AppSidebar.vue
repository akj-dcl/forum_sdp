<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { BookMarkedIcon, BookOpen, BrickWall, BriefcaseBusiness, Cog, FolderGit2, HandPlatter, LayoutGrid, Shield, ShieldPlus, Trophy, UserRoundCog } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();
// Mengambil data role dari Inertia
const userRoles = computed(() => page.props.auth?.user?.role || []);

// Fungsinya lebih kebal error, bisa handle string maupun array
const hasRole = (role: string) => {
    if (Array.isArray(userRoles.value)) {
        return userRoles.value.includes(role);
    }
    return userRoles.value === role;
};

const kanwilNavItems: NavItem[] = [
    {
        title: 'Dashboard Kanwil',
        href: '/kanwil/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Manajemen Roles',
        icon: UserRoundCog,
        href: '/admin/roles',
        },
    {
        title: 'Manajemen User',
        icon: UserRoundCog,
        href: '/admin/data-akun',
    },
    {
        title: 'Data Master Registrasi',
        icon: UserRoundCog,
        children: [
            { title: 'Data Registrasi Umum', href: '/admin/registrasi-umums' },
            { title: 'Data Registrasi Integrasi', href: '/admin/registrasi-integrasis' },
            { title: 'Data Registrasi Pidum', href: '/admin/registrasi-pidums' },
            { title: 'Data Registrasi Pidsus', href: '/admin/registrasi-pidsuses' },
            { title: 'Data Registrasi Over Staying', href: '/admin/registrasi-overstayings' },
            { title: 'Data Registrasi Identitas', href: '/admin/registrasi-identitases' },
            { title: 'Data Registrasi Pendidikan', href: '/admin/registrasi-pendidikans' },
            { title: 'Data Registrasi Detail Napi', href: '/admin/registrasi-detail-napis' },
            { title: 'Data Registrasi Detail Tahanan', href: '/admin/registrasi-detail-tahanans' },
            { title: 'Data Registrasi Petugas', href: '/admin/registrasi-petugases' },
            { title: 'Data Registrasi Pengunjung', href: '/admin/registrasi-pengunjungs' },
            { title: 'Data Registrasi WBP Dikunjungi', href: '/admin/registrasi-wbp-dikunjungis' },
            { title: 'Data Registrasi WBP Vidcall', href: '/admin/registrasi-wbp-vidcalls' },
            { title: 'Data Registrasi WBP Wartel', href: '/admin/registrasi-wbp-wartels' },
            { title: 'Data Registrasi Barang Titipan', href: '/admin/registrasi-barang-titipans' },
        ]
    },
    {
        title: 'Data Master',
        icon: UserRoundCog,
        children: [
            { title: 'Data Kanwil', href: '/admin/kanwils' },
            { title: 'Data UPT', href: '/admin/upts' },
            { title: 'Data Jenis Pidana', href: '/admin/jenis-pidanas' },
            { title: 'Data Jenis Kepribadian', href: '/admin/jenis-kepribadians' },
            { title: 'Data Jenis Kemandirian', href: '/admin/jenis-kemandirians' },
            { title: 'Data Jenis Remisi', href: '/admin/jenis-remisis' },
            { title: 'Data Jenis Pemindahan', href: '/admin/jenis-pemindahans' },
            { title: 'Data Jenis Pengawalan', href: '/admin/jenis-pengawalans' },
            { title: 'Data Jenis Klasifikasi', href: '/admin/jenis-klasifikasis' },
            { title: 'Data Jenis Personel', href: '/admin/jenis-personels' },
            { title: 'Data Jenis Pendidikan', href: '/admin/jenis-pendidikans' },
            { title: 'Data Jenis Usulan', href: '/admin/jenis-usulans' },
            { title: 'Data Jenis Struktural', href: '/admin/jenis-strukturals' },
            { title: 'Data Jenis Golongan', href: '/admin/jenis-golongans' },
            { title: 'Data Jenis Beban Kerja', href: '/admin/jenis-bebans' },
            { title: 'Data Jenis Staff Pengamanan', href: '/admin/jenis-staffs' },
            { title: 'Data Jenis Integrasi', href: '/admin/jenis-integrasis' },
            { title: 'Data Jenis Agama', href: '/admin/jenis-agamas' },
            { title: 'Data Jenis Pengeluaran', href: '/admin/jenis-pengeluarans' },

        ]
    },
    {
        title: 'Booking Rapat',
        icon: BookMarkedIcon,
        href: '/admin/data-booking-rapats',
    },
];

const uptNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Pembinaan',
        icon: HandPlatter,
        children: [
            { title: 'Registrasi', href: '/admin/data-registrasis' },
            { title: 'Residivis', href: '/admin/data-residivises' },
            {
                title: 'Pembinaan',
                href: '/pembinaan/dashboard',
                children: [
                    { title: 'Kepribadian', href: '/admin/data-pembinaan-kepribadians' },
                    { title: 'Kemandirian', href: '/admin/data-pembinaan-kemandirians' },
                ]
            },
            {
                title: 'Integrasi',
                children: [
                    { title: 'TPP UPT', href: '/admin/data-integrasi-tpps' },
                    { title: 'Integrasi', href: '/admin/data-integrasis' },
                ]
            },
            { title: 'Remisi', href: '/admin/data-remisis' },
            { title: 'Pemindahan', href: '/admin/data-pemindahans' },
        ]
    },
    {
        title: 'Pengamanan',
        icon: Shield,
        children: [
            {
                title: 'Perawatan',
                children: [
                    { title: 'Input Data Harian', href: '/admin/data-perawatan-harians' },
                    { title: 'Input Data Bulanan', href: '/admin/data-perawatan-bulanans' },
                ]
            },
            { title: 'Data Rehabilitasi', href: '/pengamanan/DataRehabilitasi' },
            { title: 'Jumlah Riil WBP', href: '/admin/data-penghuni-riils' },
            { title: 'Data Kontrol Keliling', href: '/admin/data-kontrol-kelilings' },
            { title: 'Data Regu Pengamanan', href: '/admin/data-regu-pengamanans' },
            { title: 'Data Gangguan KAMTIB', href: '/admin/data-gangguan-kamtibs' },
            { title: 'Data Sarpras', href: '/admin/data-sarpras-keamanans' },
            { title: 'Data Satgas', href: '/admin/data-satgas-pengamanans' },
            { title: 'Data Informasi Intelegen', href: '/admin/data-informasi-intelejens' },
            { title: 'Data Pelanggaran Tatib', href: '/admin/data-pelanggaran-tatibs' },
            { title: 'Data Pengawalan', href: '/admin/data-pengawalan-pengamanans' },
        ]
    },
    {
        title: 'TU dan Umum',
        icon: BrickWall,
        children: [
            {
                title: 'SDM',
                children: [
                    { title: 'Data Harian', href: '/admin/data-harian-sdms' },
                    { title: 'Data Bulanan', href: '/admin/data-bulanan-sdms' },
                    { title: 'Data Bulanan SDM', href: '/admin/data-sdm-bulanans' },
                    { title: 'Data Dossier'}
                ]
            },
            { title: 'Data Layanan Publik', href: '/admin/data-yanlik-sdms' },
            { title: 'Data Publikasi', href: '/admin/data-publikasi-sdms' },
            { title: 'Data Mou/PKS', href: '/admin/data-mou-pks' },
            {
                title: 'Keuangan',
                children: [
                    { title: 'Data Realisasi Anggaran', href: '/admin/data-realisasi-anggarans' },
                    { title: 'Data Pengadaan Barang dan Jasa', href: '/admin/data-pengadaan-barangs' },
                ]
            },
            { title: 'Data BMN', href: '/admin/data-bmn-sdms' },
        ]
    },
    {
        title: 'Pembimbingan Kemasyarakatan',
        icon: Cog,
        children: [
            {
                title: 'Klien',
                children: [
                    { title: 'Data Klien', href: '/pembimbingan/klien/DataKlien' },
                    { title: 'Data Jenis Tindak Pidana', href: '/pembimbingan/klien/DataJenisTindakPidana' },
                    { title: 'Data Jumlah Klien Berdasarkan Wilayah', href: '/pembimbingan/klien/DataJumlahKlien' },
                    { title: 'Data Pendampingan klien', href: '/pembimbingan/klien/DataPendampinganKlien' },
                    { title: 'Data Pendampingan Diversi', href: '/pembimbingan/klien/DataPendampinganDiversi' },
                    { title: 'Data Jumlah Diversi', href: '/pembimbingan/klien/DataJumlahDiversi' },
                    { title: 'Data Permintaan Litmas', href: '/pembimbingan/klien/DataPermintaanLitmas' },
                    { title: 'Data Penyelesaian Litmas', href: '/pembimbingan/klien/DataPenyelesaianLitmas' },
                    { title: 'Data Kegiatan Pembimbingan Klien Bapas', href: '/pembimbingan/klien/DataKegiatanPembimbingan' },
                ]
            },
            { title: 'Data Assessmen', href: '/pembimbingan/Assesmen' },
            { title: 'Data Pembimbingan', href: '/pembimbingan/pembimbingan' },
            { title: 'Data Pengawasan', href: '/pembimbingan/pengawasaan' },
            { title: 'Data Pelanggaran', href: '/pembimbingan/pelanggaran' },
            { title: 'Data Griya Abhipraya', href: '/pembimbingan/griya' },
        ]
    },
    {
        title: '15 Program Akselerasi',
        icon: Trophy,
        children: [
            { title: 'Akselerasi 6', href: '/programaksi/akselerasi6' },
            { title: 'Akselerasi 7', href: '/programaksi/akselerasi7' },
            { title: 'Akselerasi 8', href: '/programaksi/akselerasi8' },
            { title: 'Akselerasi 9', href: '/programaksi/akselerasi9' },
            { title: 'Akselerasi 10', href: '/programaksi/akselerasi10' },
            { title: 'Akselerasi 11', href: '/programaksi/akselerasi11' },
            { title: 'Akselerasi 13', href: '/programaksi/akselerasi13' },
        ]
    },
];

const footerNavItems: NavItem[] = [
    { title: 'Github Repo', href: 'https://github.com/laravel/vue-starter-kit', icon: FolderGit2 },
    { title: 'Documentation', href: 'https://laravel.com/docs/starter-kits#vue', icon: BookOpen },
]

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <!-- <div class="p-4 text-xs bg-red-100 text-red-800 break-words">
                DEBUG ROLE: {{ userRoles }}
            </div> -->
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain 
                v-if="hasRole('Super Admin') || hasRole('Admin Kanwil')"
                title="Menu Kanwil" 
                :items="kanwilNavItems" 
                :key="'kanwil-' + $page.url" 
            />

            <NavMain 
                v-if="hasRole('Super Admin') || hasRole('Admin Kanwil') || hasRole('Admin UPT')"
                title="Menu UPT" 
                :items="uptNavItems" 
                :key="'upt-' + $page.url" 
            />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
