import { createInertiaApp } from '@inertiajs/svelte';
import AppLayout from '@/layouts/AppLayout.svelte';
import AuthLayout from '@/layouts/AuthLayout.svelte';
import SettingsLayout from '@/layouts/settings/Layout.svelte';
import { initializeFlashToast } from '@/lib/flash-toast';
import { initializeLocale } from '@/lib/i18n.svelte';
import { initializeTheme } from '@/lib/theme.svelte';

const browserTitle = 'محفظتي | لكل ريال وجهة';

createInertiaApp({
    title: () => browserTitle,
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will set the locale and text direction on page load...
initializeLocale();

// This will listen for flash toast data from the server...
initializeFlashToast();
