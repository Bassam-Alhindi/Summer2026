<script lang="ts">
    import Languages from 'lucide-svelte/icons/languages';
    import { getLocale, setLocale } from '@/lib/i18n.svelte';

    const currentLang = $derived(getLocale());

    function toggleLanguage() {
        setLocale(currentLang === 'ar' ? 'en' : 'ar');
    }
</script>

<button
    type="button"
    onclick={toggleLanguage}
    class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium text-foreground hover:bg-accent transition-colors select-none cursor-pointer outline-none"
>
    <span>{currentLang === 'ar' ? 'English' : 'العربية'}</span>
    <Languages class="size-4 text-muted-foreground" />
</button>