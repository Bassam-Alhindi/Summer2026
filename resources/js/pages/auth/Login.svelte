<script module lang="ts">
    export const layout = {
        title: 'Log in',
        description: 'Welcome back! Please enter your details below.',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasskeyVerify from '@/components/PasskeyVerify.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { register } from '@/routes';
    import { store } from '@/routes/login';
    import { request } from '@/routes/password';
    import { ArrowRight } from 'lucide-svelte';

    let {
        status = '',
        canResetPassword,
    }: {
        status?: string;
        canResetPassword: boolean;
    } = $props();
</script>

<AppHead title="Log in" />

<div class="relative w-full max-w-md mx-auto">
    {#if status}
        <div class="mb-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-3.5 text-center text-xs font-semibold text-emerald-400 backdrop-blur-xl">
            {status}
        </div>
    {/if}

    <PasskeyVerify />

    <!-- Glassmorphic Card Container -->
    <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-white/[0.05] via-white/[0.02] to-transparent p-6 sm:p-8 backdrop-blur-2xl shadow-[0_8px_32px_0_rgba(0,0,0,0.5)]">
        
        <!-- Soft Calm Ambient Glows (إضاءة خلفية هادئة جداً وثابتة) -->
        <div class="pointer-events-none absolute -right-16 -top-16 size-48 rounded-full bg-violet-600/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-16 -bottom-16 size-48 rounded-full bg-cyan-500/10 blur-3xl"></div>

        <Form
            {...store.form()}
            resetOnSuccess={['password']}
            class="relative z-10 flex flex-col gap-6"
        >
            {#snippet children({ errors, processing })}
                <div class="grid gap-5">
                    
                    <!-- Email Field -->
                    <div class="grid gap-2">
                        <Label for="email" class="text-xs font-bold text-slate-200">
                            Email address
                        </Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autocomplete="email"
                            placeholder="email@example.com"
                            class="h-11 rounded-xl border-white/10 bg-white/[0.04] text-slate-100 placeholder:text-slate-500 focus-visible:border-violet-500/50 focus-visible:ring-violet-500/30 backdrop-blur-md transition-all"
                        />
                        <InputError message={errors.email} class="text-xs text-rose-400" />
                    </div>

                    <!-- Password Field -->
                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password" class="text-xs font-bold text-slate-200">
                                Password
                            </Label>
                            {#if canResetPassword}
                                <TextLink 
                                    href={request()} 
                                    class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors"
                                >
                                    Forgot your password?
                                </TextLink>
                            {/if}
                        </div>
                        <PasswordInput
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Password"
                            class="h-11 rounded-xl border-white/10 bg-white/[0.04] text-slate-100 placeholder:text-slate-500 focus-visible:border-violet-500/50 focus-visible:ring-violet-500/30 backdrop-blur-md transition-all"
                        />
                        <InputError message={errors.password} class="text-xs text-rose-400" />
                    </div>

                    <!-- Remember Me Option -->
                    <div class="flex items-center justify-between pt-1">
                        <Label for="remember" class="flex items-center gap-2.5 cursor-pointer text-xs text-slate-300 hover:text-slate-100 transition-colors">
                            <Checkbox 
                                id="remember" 
                                name="remember" 
                                class="border-white/20 data-[state=checked]:bg-gradient-to-r data-[state=checked]:from-violet-600 data-[state=checked]:to-cyan-500 data-[state=checked]:border-transparent rounded-md"
                            />
                            <span>Remember me</span>
                        </Label>
                    </div>

                    <!-- Calm & Subtle Premium Button -->
                    <div class="mt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            data-test="login-button"
                            class="group relative w-full overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600/40 via-cyan-500/40 to-indigo-600/40 p-px transition-all duration-300 hover:from-violet-500 hover:via-cyan-400 hover:to-indigo-500 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none shadow-md hover:shadow-violet-600/20"
                        >
                            <div class="relative flex h-11 w-full items-center justify-center gap-2 rounded-[15px] bg-[#0b0b14]/90 backdrop-blur-2xl px-4 text-xs font-extrabold text-white transition-colors duration-300 group-hover:bg-[#0b0b14]/75">
                                {#if processing}
                                    <Spinner class="size-4 text-white" />
                                {/if}
                                <span>Log in</span>
                                <ArrowRight class="size-4 text-cyan-400 transition-transform duration-300 group-hover:translate-x-1" />
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Sign up Link -->
                <div class="mt-2 text-center text-xs text-slate-400">
                    Don't have an account?
                    <TextLink 
                        href={register()} 
                        class="font-bold text-cyan-400 hover:text-cyan-300 ms-1 transition-colors"
                    >
                        Sign up
                    </TextLink>
                </div>
            {/snippet}
        </Form>
    </div>
</div>