<script module lang="ts">
    export const layout = {
        title: 'Get started',
        description: 'Enter your details below to create your account',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { login } from '@/routes';
    import { store } from '@/routes/register';
    import { ArrowRight } from 'lucide-svelte';

    let { passwordRules }: { passwordRules: string } = $props();
</script>

<AppHead title="Register" />

<div class="relative w-full max-w-md mx-auto">
    <!-- Glassmorphic Card Container -->
    <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-white/[0.05] via-white/[0.02] to-transparent p-6 sm:p-8 backdrop-blur-2xl shadow-[0_8px_32px_0_rgba(0,0,0,0.5)]">
        
        <!-- Soft Calm Ambient Glows -->
        <div class="pointer-events-none absolute -right-16 -top-16 size-48 rounded-full bg-violet-600/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-16 -bottom-16 size-48 rounded-full bg-cyan-500/10 blur-3xl"></div>

        <Form
            {...store.form()}
            resetOnSuccess={['password', 'password_confirmation']}
            class="relative z-10 flex flex-col gap-6"
        >
            {#snippet children({ errors, processing })}
                <div class="grid gap-5">
                    
                    <!-- Name Field -->
                    <div class="grid gap-2">
                        <Label for="name" class="text-xs font-bold text-slate-200">
                            Name
                        </Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autocomplete="name"
                            name="name"
                            placeholder="Full name"
                            class="h-11 rounded-xl border-white/10 bg-white/[0.04] text-slate-100 placeholder:text-slate-500 focus-visible:border-violet-500/50 focus-visible:ring-violet-500/30 backdrop-blur-md transition-all"
                        />
                        <InputError message={errors.name} class="text-xs text-rose-400" />
                    </div>

                    <!-- Email Field -->
                    <div class="grid gap-2">
                        <Label for="email" class="text-xs font-bold text-slate-200">
                            Email address
                        </Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            autocomplete="email"
                            name="email"
                            placeholder="email@example.com"
                            class="h-11 rounded-xl border-white/10 bg-white/[0.04] text-slate-100 placeholder:text-slate-500 focus-visible:border-violet-500/50 focus-visible:ring-violet-500/30 backdrop-blur-md transition-all"
                        />
                        <InputError message={errors.email} class="text-xs text-rose-400" />
                    </div>

                    <!-- Password Field -->
                    <div class="grid gap-2">
                        <Label for="password" class="text-xs font-bold text-slate-200">
                            Password
                        </Label>
                        <PasswordInput
                            id="password"
                            required
                            autocomplete="new-password"
                            name="password"
                            placeholder="Password"
                            passwordrules={passwordRules}
                            class="h-11 rounded-xl border-white/10 bg-white/[0.04] text-slate-100 placeholder:text-slate-500 focus-visible:border-violet-500/50 focus-visible:ring-violet-500/30 backdrop-blur-md transition-all"
                        />
                        <InputError message={errors.password} class="text-xs text-rose-400" />
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="grid gap-2">
                        <Label for="password_confirmation" class="text-xs font-bold text-slate-200">
                            Confirm password
                        </Label>
                        <PasswordInput
                            id="password_confirmation"
                            required
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="Confirm password"
                            passwordrules={passwordRules}
                            class="h-11 rounded-xl border-white/10 bg-white/[0.04] text-slate-100 placeholder:text-slate-500 focus-visible:border-violet-500/50 focus-visible:ring-violet-500/30 backdrop-blur-md transition-all"
                        />
                        <InputError message={errors.password_confirmation} class="text-xs text-rose-400" />
                    </div>

                    <!-- Calm & Subtle Premium Button -->
                    <div class="mt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            data-test="register-user-button"
                            class="group relative w-full overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600/40 via-cyan-500/40 to-indigo-600/40 p-px transition-all duration-300 hover:from-violet-500 hover:via-cyan-400 hover:to-indigo-500 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none shadow-md hover:shadow-violet-600/20"
                        >
                            <div class="relative flex h-11 w-full items-center justify-center gap-2 rounded-[15px] bg-[#0b0b14]/90 backdrop-blur-2xl px-4 text-xs font-extrabold text-white transition-colors duration-300 group-hover:bg-[#0b0b14]/75">
                                {#if processing}
                                    <Spinner class="size-4 text-white" />
                                {/if}
                                <span>Create account</span>
                                <ArrowRight class="size-4 text-cyan-400 transition-transform duration-300 group-hover:translate-x-1" />
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Log in Link -->
                <div class="mt-2 text-center text-xs text-slate-400">
                    Already have an account?
                    <TextLink 
                        href={login()} 
                        class="font-bold text-cyan-400 hover:text-cyan-300 ms-1 transition-colors"
                    >
                        Log in
                    </TextLink>
                </div>
            {/snippet}
        </Form>
    </div>
</div>