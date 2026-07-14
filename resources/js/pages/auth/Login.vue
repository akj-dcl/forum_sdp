<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Log in - 4C Cozy Corner" />

    <div class="relative flex min-h-screen flex-col items-center justify-center bg-slate-950 selection:bg-primary selection:text-on-primary px-4 sm:px-6 lg:px-8 font-sans" style="font-family: 'Inter', sans-serif;">
        
        <!-- Glowing Ambient Background Effects -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none select-none">
            <div class="absolute -top-[30%] -left-[10%] w-[70%] h-[70%] rounded-full bg-primary/20 blur-3xl"></div>
            <div class="absolute bottom-[15%] -right-[10%] w-[50%] h-[50%] rounded-full bg-violet-900/10 blur-3xl"></div>
        </div>

        <!-- Glassmorphic Login Card -->
        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/50 p-8 shadow-2xl backdrop-blur-xl sm:p-10 text-slate-100">
            
            <div class="mb-8 text-center select-none">
                <Link href="/" class="inline-flex items-center gap-3 mb-6 transition-transform hover:scale-105 no-underline">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary to-violet-600 flex items-center justify-center shadow-lg shadow-primary/20 shrink-0">
                        <span class="material-symbols-outlined text-white font-bold" style="font-variation-settings: 'FILL' 1;">forum</span>
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-xl font-extrabold text-white tracking-tight leading-none">4C</span>
                        <span class="text-[9px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Cozy Corner</span>
                    </div>
                </Link>
                <h2 class="text-2xl font-bold text-white">Selamat Datang Kembali</h2>
                <p class="mt-2 text-xs text-slate-400 leading-normal">Silakan masukkan username dan password Anda untuk masuk</p>
            </div>

            <div
                v-if="status"
                class="mb-6 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-center text-sm font-medium text-emerald-400"
            >
                {{ status }}
            </div>

            <Form
                v-bind="store.form()"
                :reset-on-success="['password']"
                v-slot="{ errors, processing }"
                class="flex flex-col gap-5"
            >
                <div class="grid gap-5">
                    <!-- Username Field -->
                    <div class="grid gap-2">
                        <Label for="username" class="text-slate-300 font-semibold text-xs">Username</Label>
                        <Input
                            id="username"
                            type="text"
                            name="username"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="username"
                            placeholder="Masukkan username Anda"
                            class="border-slate-800 bg-slate-950/70 text-slate-100 placeholder:text-slate-500 focus-visible:border-primary focus-visible:ring-1 focus-visible:ring-primary rounded-xl h-11 transition-all"
                        />
                        <InputError :message="errors.username" />
                    </div>

                    <!-- Password Field -->
                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password" class="text-slate-300 font-semibold text-xs">Password</Label>
                            <Link
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-xs font-semibold text-primary transition-colors hover:text-opacity-80 no-underline"
                                :tabindex="5"
                            >
                                Lupa password?
                            </Link>
                        </div>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="border-slate-800 bg-slate-950/70 text-slate-100 placeholder:text-slate-500 focus-visible:border-primary focus-visible:ring-1 focus-visible:ring-primary rounded-xl h-11 transition-all"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <!-- Remember Me Option -->
                    <div class="flex items-center justify-between mt-1 select-none">
                        <Label for="remember" class="flex items-center space-x-3 cursor-pointer">
                            <Checkbox 
                                id="remember" 
                                name="remember" 
                                :tabindex="3" 
                                class="border-slate-700 data-[state=checked]:bg-primary data-[state=checked]:border-primary data-[state=checked]:text-white rounded"
                            />
                            <span class="text-xs font-medium text-slate-300">Ingat saya</span>
                        </Label>
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        class="mt-4 w-full bg-primary hover:bg-opacity-95 text-sm font-bold text-white shadow-lg shadow-primary/20 hover:scale-[1.01] active:scale-[0.99] transition-all rounded-xl h-11 border-none cursor-pointer flex items-center justify-center"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" class="mr-2 h-4 w-4 border-white border-t-transparent" />
                        Masuk Sistem
                    </Button>
                </div>

                <!-- Registration Trigger -->
                <div
                    class="mt-2 text-center text-xs text-slate-400"
                    v-if="canRegister"
                >
                    Belum memiliki akun?
                    <Link :href="register()" :tabindex="5" class="font-bold text-primary hover:text-opacity-80 transition-colors no-underline">
                        Daftar di sini
                    </Link>
                </div>
            </Form>
        </div>

        <footer class="mt-8 text-center text-[10px] text-slate-600 select-none">
            &copy; {{ new Date().getFullYear() }} Cozy Corner Correctional Club (4C). Hak Cipta Dilindungi.
        </footer>
    </div>
</template>