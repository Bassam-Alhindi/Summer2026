import { translations, type Language, type TranslationKey } from './translations';

const STORAGE_KEY = 'locale';

const locale = $state<{ value: Language }>({ value: 'en' });

const setCookie = (name: string, value: string, days = 365): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const applyDirection = (lang: Language): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const dir = lang === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.dir = dir;
    document.documentElement.lang = lang;
};

const getStoredLocale = (): Language => {
    if (typeof window === 'undefined') {
        return 'en';
    }

    const stored = localStorage.getItem(STORAGE_KEY);
    return stored === 'ar' ? 'ar' : 'en';
};

export function initializeLocale(): void {
    if (typeof window === 'undefined') {
        return;
    }

    const lang = getStoredLocale();
    locale.value = lang;
    applyDirection(lang);
}

export function setLocale(lang: Language): void {
    locale.value = lang;

    if (typeof window !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, lang);
    }

    setCookie(STORAGE_KEY, lang);
    applyDirection(lang);
}

export function getLocale(): Language {
    return locale.value;
}

export function isRTL(): boolean {
    return locale.value === 'ar';
}

export function t(key: TranslationKey): string {
    return translations[locale.value]?.[key] ?? translations.en[key] ?? key;
}

export function toggleLocale(): void {
    setLocale(locale.value === 'en' ? 'ar' : 'en');
}