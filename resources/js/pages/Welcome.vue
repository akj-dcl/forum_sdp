<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Selamat Datang - PUSDAPAS" />

    <div class="relative flex min-h-screen flex-col items-center justify-center bg-slate-900 selection:bg-yellow-500 selection:text-slate-900">
        
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[30%] -left-[10%] w-[70%] h-[70%] rounded-full bg-blue-800/20 blur-3xl"></div>
            <div class="absolute top-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-yellow-600/10 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-7xl px-6 lg:px-8">
            <header class="flex items-center justify-between py-6 w-full">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-white tracking-wider">
                        PUS<span class="text-yellow-500">DAPAS</span>
                    </span>
                </div>

                <nav class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-900 transition-all duration-300 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-lg hover:from-yellow-300 hover:to-yellow-500 shadow-lg shadow-yellow-500/30"
                    >
                        Masuk Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="px-5 py-2 text-sm font-medium text-slate-300 transition-colors hover:text-white"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="px-6 py-2.5 text-sm font-semibold text-slate-900 transition-all duration-300 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-lg hover:from-yellow-300 hover:to-yellow-500 shadow-lg shadow-yellow-500/30"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </header>

            <main class="mt-16 sm:mt-24 lg:mt-32 flex flex-col items-center text-center">
                <div class="mb-8 inline-flex items-center rounded-full border border-yellow-500/30 bg-yellow-500/10 px-3 py-1 text-sm text-yellow-400">
                    <span class="flex w-2 h-2 rounded-full bg-yellow-500 mr-2"></span>
                    Sistem Informasi Manajemen Terpadu
                </div>

                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-6xl lg:text-7xl max-w-4xl leading-tight">
                    Pusat Data Pemasyarakatan <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 via-yellow-500 to-yellow-600">
                        Nusa Tenggara Barat
                    </span>
                </h1>
                
                <p class="mt-6 text-lg leading-8 text-slate-300 max-w-2xl">
                    Platform digital terintegrasi untuk pengelolaan data warga binaan, program pembinaan, dan administrasi pemasyarakatan secara efektif dan efisien.
                </p>

                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="login()"
                        class="rounded-lg bg-gradient-to-r from-yellow-400 to-yellow-600 px-8 py-3.5 text-base font-semibold text-slate-900 shadow-lg shadow-yellow-500/30 hover:from-yellow-300 hover:to-yellow-500 transition-all duration-300"
                    >
                        Mulai Akses Sistem
                    </Link>
                    <a href="#features" class="text-sm font-semibold leading-6 text-slate-300 hover:text-white transition-colors">
                        Pelajari Lebih Lanjut <span aria-hidden="true">→</span>
                    </a>
                </div>
            </main>

            <div class="mt-24 sm:mt-32 grid grid-cols-1 gap-8 sm:grid-cols-3 max-w-5xl mx-auto pb-16">
                <div class="rounded-2xl border border-slate-700 bg-slate-800/50 p-6 backdrop-blur-sm">
                    <div class="h-10 w-10 rounded-lg bg-blue-500/20 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Manajemen Data Akurat</h3>
                    <p class="mt-2 text-sm text-slate-400">Pencatatan data WBP yang terpusat dan terstruktur dengan baik.</p>
                </div>
                
                <div class="rounded-2xl border border-slate-700 bg-slate-800/50 p-6 backdrop-blur-sm">
                    <div class="h-10 w-10 rounded-lg bg-yellow-500/20 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Monitoring Pembinaan</h3>
                    <p class="mt-2 text-sm text-slate-400">Pantau perkembangan program pembinaan kepribadian dan kemandirian.</p>
                </div>

                <div class="rounded-2xl border border-slate-700 bg-slate-800/50 p-6 backdrop-blur-sm">
                    <div class="h-10 w-10 rounded-lg bg-green-500/20 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Sistem Keamanan Terpadu</h3>
                    <p class="mt-2 text-sm text-slate-400">Hak akses yang diatur ketat berdasarkan peran dan wilayah kerja UPT.</p>
                </div>
            </div>
        </div>

        <footer class="absolute bottom-4 text-sm text-slate-500 z-10 text-center w-full">
            &copy; {{ new Date().getFullYear() }} Kanwil Ditjenpas NTB. Hak Cipta Dilindungi.
        </footer>
    </div>
</template>