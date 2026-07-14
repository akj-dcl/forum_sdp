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
    <Head title="4C - Cozy Corner Correctional Club" />

    <div class="relative flex min-h-screen flex-col items-center justify-between bg-slate-950 selection:bg-primary selection:text-on-primary overflow-hidden text-slate-100 font-sans" style="font-family: 'Inter', sans-serif;">
        
        <!-- Glowing Ambient Background Effects -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none select-none">
            <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] rounded-full bg-indigo-900/20 blur-3xl"></div>
            <div class="absolute top-[40%] right-[-10%] w-[50%] h-[50%] rounded-full bg-violet-900/10 blur-3xl"></div>
            <div class="absolute bottom-[-10%] left-[20%] w-[40%] h-[40%] rounded-full bg-blue-900/10 blur-3xl"></div>
        </div>

        <!-- Glassmorphic Top Navbar -->
        <header class="relative z-10 w-full max-w-7xl px-6 lg:px-8 flex items-center justify-between py-6 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary to-violet-600 flex items-center justify-center shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-white font-bold" style="font-variation-settings: 'FILL' 1;">forum</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-extrabold text-white tracking-tight leading-none">4C</span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Cozy Corner</span>
                </div>
            </div>

            <nav class="flex items-center gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="px-6 py-2.5 text-sm font-semibold text-white transition-all duration-300 bg-primary hover:bg-opacity-90 rounded-xl shadow-lg shadow-primary/20"
                >
                    Masuk Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="px-5 py-2.5 text-sm font-semibold text-slate-300 hover:text-white transition-colors"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="px-6 py-2.5 text-sm font-semibold text-white transition-all duration-300 bg-primary hover:bg-opacity-90 rounded-xl shadow-lg shadow-primary/20"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <!-- Main Hero -->
        <main class="relative z-10 w-full max-w-7xl px-6 lg:px-8 flex-1 flex flex-col items-center justify-center text-center py-12">
            <div class="mb-6 inline-flex items-center rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold text-primary tracking-wide select-none">
                <span class="flex w-2 h-2 rounded-full bg-primary mr-2 animate-pulse"></span>
                INTERNAL ANNOUNCEMENT & COLLABORATION HUBS
            </div>

            <h1 class="text-5xl font-extrabold tracking-tight text-white sm:text-7xl lg:text-8xl max-w-5xl leading-tight">
                Cozy Corner <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-indigo-400 to-violet-500">
                    Correctional Club
                </span>
            </h1>
            
            <p class="mt-6 text-lg sm:text-xl leading-relaxed text-slate-300 max-w-3xl">
                Platform komunikasi internal, penyebaran informasi cepat (broadcast), serta ruang diskusi terpusat untuk meningkatkan kolaborasi, keakraban, dan integrasi kinerja pegawai pemasyarakatan.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 select-none">
                <Link
                    v-if="!$page.props.auth.user"
                    :href="login()"
                    class="w-full sm:w-auto rounded-xl bg-primary px-8 py-4 text-base font-bold text-white shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300"
                >
                    Mulai Akses Kolaborasi
                </Link>
                <Link
                    v-else
                    :href="dashboard()"
                    class="w-full sm:w-auto rounded-xl bg-primary px-8 py-4 text-base font-bold text-white shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300"
                >
                    Masuk Dashboard
                </Link>
                <a href="#features" class="w-full sm:w-auto text-sm font-bold leading-6 text-slate-300 hover:text-white transition-colors py-3.5 px-6 rounded-xl hover:bg-slate-900/50 border border-transparent hover:border-slate-800/80">
                    Pelajari Fitur 4C <span aria-hidden="true">↓</span>
                </a>
            </div>

            <!-- Features Grid -->
            <div id="features" class="mt-28 w-full max-w-6xl grid grid-cols-1 gap-6 sm:grid-cols-3 text-left">
                <!-- Feature 1 -->
                <div class="rounded-2xl border border-slate-800/80 bg-slate-900/40 p-6 backdrop-blur-md shadow-sm hover:border-primary/30 hover:bg-slate-900/60 transition-all duration-300 flex flex-col gap-4 group">
                    <div class="h-12 w-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner shadow-primary/5">
                        <span class="material-symbols-outlined text-[24px]">rss_feed</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white mb-1.5 group-hover:text-primary transition-colors">Broadcast & Comments</h3>
                        <p class="text-sm leading-relaxed text-slate-400">Bagikan pengumuman atau info kegiatan dinamis secara cepat, lengkap dengan lampiran file, foto, video, like, serta utas komentar diskusi terarah.</p>
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="rounded-2xl border border-slate-800/80 bg-slate-900/40 p-6 backdrop-blur-md shadow-sm hover:border-primary/30 hover:bg-slate-900/60 transition-all duration-300 flex flex-col gap-4 group">
                    <div class="h-12 w-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner shadow-indigo-500/5">
                        <span class="material-symbols-outlined text-[24px]">forum</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white mb-1.5 group-hover:text-indigo-400 transition-colors">Direct Messages Instan</h3>
                        <p class="text-sm leading-relaxed text-slate-400">Komunikasi personal aman dan instan dengan sesama rekan kerja melalui obrolan pesan langsung bergaya modern, lengkap dengan penanda bacaan.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="rounded-2xl border border-slate-800/80 bg-slate-900/40 p-6 backdrop-blur-md shadow-sm hover:border-primary/30 hover:bg-slate-900/60 transition-all duration-300 flex flex-col gap-4 group">
                    <div class="h-12 w-12 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner shadow-violet-500/5">
                        <span class="material-symbols-outlined text-[24px]">supervised_user_circle</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white mb-1.5 group-hover:text-violet-400 transition-colors">Direktori Kehadiran</h3>
                        <p class="text-sm leading-relaxed text-slate-400">Daftar rekan kerja yang sedang online di UPT, Kantor Wilayah, maupun Kantor Pusat terdeteksi secara real-time untuk mempermudah koordinasi tugas.</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Premium Footer -->
        <footer class="relative z-10 w-full text-center py-6 text-xs text-slate-500 border-t border-slate-900 shrink-0 select-none">
            &copy; {{ new Date().getFullYear() }} Cozy Corner Correctional Club (4C). Hak Cipta Dilindungi.
        </footer>
    </div>
</template>