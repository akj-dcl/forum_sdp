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
    <Head title="Log in - PUSDAPAS" />

    <div class="relative flex min-h-screen flex-col items-center justify-center bg-slate-900 selection:bg-yellow-500 selection:text-slate-900 px-4 sm:px-6 lg:px-8">
        
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[30%] -left-[10%] w-[70%] h-[70%] rounded-full bg-blue-800/20 blur-3xl"></div>
            <div class="absolute bottom-[10%] -right-[10%] w-[50%] h-[50%] rounded-full bg-yellow-600/10 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-2xl border border-slate-700 bg-slate-800/60 p-8 shadow-2xl backdrop-blur-xl sm:p-10">
            
            <div class="mb-8 text-center">
                <Link href="/" class="inline-block mb-6 transition-transform hover:scale-105">
                    <span class="text-3xl font-bold text-white tracking-wider">
                        PUS<span class="text-yellow-500">DAPAS</span>
                    </span>
                </Link>
                <h2 class="text-2xl font-bold text-white">Selamat Datang Kembali</h2>
                <p class="mt-2 text-sm text-slate-400">Silakan masukkan username dan password Anda</p>
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
                    <div class="grid gap-2">
                        <Label for="username" class="text-slate-300">Username</Label>
                        <Input
                            id="username"
                            type="text"
                            name="username"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="username"
                            placeholder="Masukkan username"
                            class="border-slate-600 bg-slate-900/50 text-white placeholder:text-slate-500 focus-visible:border-yellow-500 focus-visible:ring-1 focus-visible:ring-yellow-500"
                        />
                        <InputError :message="errors.username" />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password" class="text-slate-300">Password</Label>
                            <Link
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm font-medium text-yellow-500 transition-colors hover:text-yellow-400"
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
                            class="border-slate-600 bg-slate-900/50 text-white placeholder:text-slate-500 focus-visible:border-yellow-500 focus-visible:ring-1 focus-visible:ring-yellow-500"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="flex items-center justify-between mt-1">
                        <Label for="remember" class="flex items-center space-x-3 cursor-pointer">
                            <Checkbox 
                                id="remember" 
                                name="remember" 
                                :tabindex="3" 
                                class="border-slate-500 data-[state=checked]:bg-yellow-500 data-[state=checked]:border-yellow-500 data-[state=checked]:text-slate-900"
                            />
                            <span class="text-sm font-normal text-slate-300">Ingat saya</span>
                        </Label>
                    </div>

                    <Button
                        type="submit"
                        class="mt-4 w-full bg-gradient-to-r from-yellow-400 to-yellow-600 text-base font-bold text-slate-900 shadow-lg shadow-yellow-500/20 transition-all hover:from-yellow-300 hover:to-yellow-500"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" class="mr-2 h-4 w-4 border-slate-900 border-t-transparent" />
                        Masuk Sistem
                    </Button>
                </div>

                <div
                    class="mt-2 text-center text-sm text-slate-400"
                    v-if="canRegister"
                >
                    Belum memiliki akun?
                    <Link :href="register()" :tabindex="5" class="font-medium text-yellow-500 hover:text-yellow-400 transition-colors">
                        Daftar di sini
                    </Link>
                </div>
            </Form>
        </div>
    </div>
</template>